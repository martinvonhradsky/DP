#!/usr/bin/env python
import os
import requests
import pandas as pd
import psycopg2
import time
from sqlalchemy import create_engine

# Define database credentials as environment variables
POSTGRES_DB = os.getenv('POSTGRES_DB')
POSTGRES_USER = os.getenv('POSTGRES_USER')
POSTGRES_PASSWORD = os.getenv('POSTGRES_PASSWORD')
DB_HOST = os.getenv('DB_HOST')

print('Sleep 10s to ensure database is already setup')
time.sleep(10)

try:
    conn = psycopg2.connect(
        dbname=POSTGRES_DB,
        user=POSTGRES_USER,
        password=POSTGRES_PASSWORD,
        host=DB_HOST
    )
except psycopg2.Error as e:
    raise Exception(f"Could not connect to database: {e}")

#######################################################
# Creating table for storing 
def createTableTarget():
    try:
        # Create the tests table if it doesn't already exist
        conn.autocommit = True
        cur = conn.cursor()
        cur.execute("SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'target');")
        table_exists = cur.fetchone()[0]

        # If table exists, drop it
        if table_exists:
            print("Database Target already exists. Nothing to do")

        else: # Else create DB
            print("Table doesn't exist: Creating table Target")
            cur.execute("""
                CREATE TABLE target (
                alias varchar(50) UNIQUE,
                IP VARCHAR(100) NOT NULL,
                sudo_user VARCHAR(50) NOT NULL,
                password VARCHAR(50) NOT NULL,
                platform VARCHAR(20) NOT NULL
                );
            """)
            localhost_values = ('localhost', 'localhost', 'n/a', 'n/a', 'debian')
            debian_values = ('debian', 'test-target-debian', 'test', 'password', 'debian')
            centos_values = ('centos', 'test-target-centos', 'test', 'password', 'centos')
            insert_query = """
                INSERT INTO target (alias, IP, sudo_user, password, platform)
                VALUES (%s, %s, %s, %s, %s)
            """
            for row in [localhost_values, debian_values, centos_values]:
                cur.execute(insert_query, row)
            conn.commit()
        cur.close()
        return 1
    except Exception as e:
        print("Error: %s", e)
        return 0


#######################################################
# Creating table for storing Test history 
def createTableHistory():
    try:
        # Create the tests table if it doesn't already exist
        conn.autocommit = True
        cur = conn.cursor()
        cur.execute("SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'history');")
        table_exists = cur.fetchone()[0]

        # If table exists, drop it
        if table_exists:
            print("Database History already exists. Nothing to do")

        else: # Else create DB
            print("Table doesn't exist: Creating table History")
            cur.execute('''
                CREATE TABLE history (
                    test_id TEXT,
                    target TEXT,
                    start_time TEXT,
                    end_time TEXT,
                    output TEXT,
                    detected BOOLEAN
                )
            ''')

        cur.close()
        return 1
    except Exception as e:
        print("Error: %s", e)
        return 0


##############################################################
# Initialize Tests table for InvokeAtomic and custom user test
# In first run - DB created and populated with InvokeAtomic data
# Rerun works as update: custom tests stay in DB while Atomics get erased and repopulated
def createTableTests():
    try:
        # Create the tests table if it doesn't already exist
        conn.autocommit = True
        cur = conn.cursor()
        cur.execute("SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'tests');")
        table_exists = cur.fetchone()[0]

        # If table exists, drop it
        if table_exists:
            print("Database Tests already exists. Dropping Atomics")
            cur.execute("DELETE FROM tests WHERE executable='Invoke atomic'")

        else: # Else create DB
            print("Table doesn't exist: Creating table Test")
            cur.execute("""
                CREATE TABLE IF NOT EXISTS tests (
                    id SERIAL PRIMARY KEY,
                    technique_id VARCHAR(15) NOT NULL,
                    test_number INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    file_name TEXT,
                    executable TEXT NOT NULL,
                    local_execution BOOLEAN,                    
                    description TEXT,
                    arguments BOOLEAN
                )
            """)
        cur.close()
        parseDataToDB()
        return 1
    except Exception as e:
        print("Error: %s", e)
        return 0

def flagMitreDB(df):
    print("Flagging MitreDB started")
    df['status'] = 'not available'
    conn.autocommit = True
    cur = conn.cursor()
    for index, row in df.iterrows():
        try:
            cur.execute("SELECT EXISTS (SELECT FROM tests WHERE technique_id = %s);", (row.id,))
            test_available = cur.fetchone()[0]
            if test_available:
                df.at[index, 'status'] = 'available'
        except Exception as e:
            print(f"Error in flagging MitreDB: {e}")
    print("Flagging MitreDB finished")
    cur.close()
    return df


def parseDataToDB():
    print("Parsing started")
    # get the directory containing the script
    dir_path = os.path.dirname(os.path.realpath(__file__))

    # read the data from the file
    try:
        with open(os.path.join('/data', 'available_tests.txt'), 'r') as file:
            data = file.read()
            data = data[:len(data) - 4].split("\", \"")
    except:
        print('Installed available_tests.txt not present, using default.')
        with open(os.path.join('/app', 'available_tests.txt'), 'r') as file:
            data = file.read()
            data = data[:len(data) - 4].split("\", \"")

    # create a new array to store Tests from InvokeAtomic
    techniques = []

    # iterate through each line in the data
    for line in data:
        # check if the line starts with "T" (indicating a technique)
        if line.startswith("T"):
            # append the technique to the techniques array
            techniques.append(line)
    if not techniques:
        print("No techniques available => no file")
    
    # Parse the data and insert it into the tests table
    for item in techniques:
        # Extract the technique ID and test number from the item
        technique_id = item.split("-")[0]
        test_number = item.split("-")[1].split(" ")[0]
        
        # Extract the name of the test
        name = item.split(" ",1)[1].strip()
        
        # Insert the data into the tests table
        try:
            cur = conn.cursor()
            cur.execute(
                "INSERT INTO tests (technique_id, test_number, name, executable, arguments, local_execution) VALUES (%s, %s, %s, %s, %s, %s)",
                (technique_id, test_number, name, "Invoke atomic", 'false', 'true')
            )
            conn.commit()
        except Exception as e:
            print(f"Failed to insert data into the database: {str(e)}")
        finally:
            cur.close()

    print("Parsing finished")


    

# Download Mitre matrix
def downloadMatrix():
    URL = "https://attack.mitre.org/docs/enterprise-attack-v13.1/enterprise-attack-v13.1.xlsx"
    # download the data behind the URL
    response = requests.get(URL)
    # Open the response into a new file
    open("matrix.xlsx", "wb").write(response.content)

def createTableMitre():

    # Create database connection

    conn.autocommit = True
    cursor = conn.cursor()

    # Check if table already exists
    cursor.execute("SELECT EXISTS (SELECT FROM pg_tables WHERE tablename = 'mitre');")
    table_exists = cursor.fetchone()[0]

    # If table exists, drop it
    if table_exists:
        print("Table already exists. Dropping table 'mitre'...")
        cursor.execute("DROP TABLE mitre;")
        print("Table mitre dropped successfully.")

    # Check if the file exists
    if os.path.exists('./matrix.xlsx'):
        print("Table mitre using local file matrix.xlsx ")
    else:
        print("Table mitre no local file matrix.xlsx available. Downloading...")
        downloadMatrix()
        

    # Create new table and insert data
    df = pd.read_excel(r'./matrix.xlsx', engine="openpyxl")
    df = df[['ID', 'name', 'description', 'url', 'tactics', 'platforms']]
    engine = create_engine(f'postgresql://{POSTGRES_USER}:{POSTGRES_PASSWORD}@{DB_HOST}/{POSTGRES_DB}')
    df.columns = df.columns.str.lower()
    df = flagMitreDB(df)
    df.to_sql('mitre', engine, if_exists='replace')

    cursor.close()


if __name__ == '__main__':
    try:
        print('Pushing to DB.')
        createTableTests()
        createTableMitre()
        createTableHistory()
        createTableTarget()
        conn.close()
        print('Finished.')
    except Exception as e:
        print(f"An error occurred: {e}")
