# dev
FROM python:3.10 AS dev

COPY requirements.txt /app/requirements.txt
RUN pip install --upgrade pip
RUN pip install -r /app/requirements.txt

WORKDIR /app

CMD [ "python", "-u", "/app/populate_db.py"]

# prod
FROM dev AS prod

COPY matrix.xlsx /app/matrix.xlsx
COPY available_tests.txt /app/available_tests.txt
COPY populate_db.py /app/populate_db.py

WORKDIR /app