<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once('database.php');

define('ANSIBLE_OUTPUT_PATH', '/data-execution-outputs');

function isID($id) {
  // Check if the ID matches the regular expression /T[0-9]+\.[0-9]+/i
  if (preg_match('/T[0-9]+\.[0-9]+/i', $id)) {
    return true;
  }
  if (preg_match('/T[0-9]+/i', $id)) {
    return true;
  }
  return false;
}

function isURLValid($url) {
  if (filter_var($url, FILTER_VALIDATE_URL)) {
      return true;
  } else {
      return false;
  }
}

# Get execution output
function getTestOutput($str) {
  // remove newline characters
  $str = str_replace("\n", '', $str);
  $str = str_replace("\r", '', $str);
  // remove extra whitespace
  $str = preg_replace('/\s+/', ' ', $str);
  // split string into parts
  $parts = explode("stdout_lines", $str);
  return substr($parts[1], 3, -2);
}

// Return 0 on success, non-zero on fail.
function saveHistory($json) {
  try {
      // Create a new database object
      $db = new Database();
      $db->connect();
      
      // Prepare the insert statement with placeholders
      $sql = "INSERT INTO history (test_id, target, start_time, end_time, output, detected)
              VALUES (:test_id, :target, :start, :end, :output, :detected)";

      $stmt = $db->prepare($sql);
      // Extract the parameters from the JSON data and bind the values to the placeholders
      $test_id = $json->testId;
      $target = $json->target;
      $now = new \DateTime('now');
      $start = $end = $now->format('D M d, Y G:i');
      $outputFileId = $json->outputFileId;
      $detected_param = $json->detected ? 'true' : 'false';

      $stmt->bindValue(':test_id', $test_id);
      $stmt->bindValue(':target', $target);
      $stmt->bindValue(':start', $start);
      $stmt->bindValue(':end', $end);
      $stmt->bindValue(':output', $outputFileId);
      $stmt->bindValue(':detected', $detected_param);

      // Execute the statement and return the test ID
      $check = $stmt->execute();
      return ($check) ? 0 : 1;
  } catch (PDOException $e) {
      echo '{"status": "error","message":  " Failed to save test history: ' . $e->getMessage() . '"}';
      return 1;
  }
}

// &$didSucceed is optional pass-by-reference variable.
function executeQuery($select, &$didSucceed = NULL) {
  $isDidSucceedUsed = (func_num_args() == 2);
  $didSucceed = $isDidSucceedUsed ? true : NULL;

  try {
      // Create a new database object
      $db = new Database();
      $db->connect();
      
      // Prepare the select statement with placeholders
      $stmt = $db->prepare($select);
      $stmt->execute();

      // Fetch all the rows and return as JSON
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($result == null){          
          echo '[]';
          return null;
      }
      return json_encode($result);
  } catch (PDOException $e) {
      $didSucceed = $isDidSucceedUsed ? false : NULL;
      return "Connection failed: " . $e->getMessage();
  }
}
function editUser($ip, $username, $password, $platform, $alias) {
  try {
    // Create a new database object
    $db = new Database();
    $db->connect();
      
    $query = "SELECT * FROM target WHERE alias = :alias ;";
    $stmt = $db->prepare($query);    
    $stmt->bindValue('alias', $alias);
    $stmt->execute();

    if ($stmt->fetchAll(PDO::FETCH_ASSOC) == null){
      echo "No such user available";
      http_response_code(400);
      return;
    }
      $query = "UPDATE target SET ";
      $params = array();

      if ($ip !== null && $ip !== "") {
          $query .= "IP = :IP, ";
          $params['IP'] = $ip;
      }
      if ($username !== null && $username !== "") {
          $query .= "sudo_user = :sudo_user, ";
          $params['sudo_user'] = $username;
      }
      if ($password !== null && $password !== "") {
          $query .= "password = :password, ";
          $params['password'] = $password;
      }
      if ($platform !== null && $platform !== "") {
          $query .= "platform = :platform, ";
          $params['platform'] = $platform;
      }
      
      // Remove trailing comma
      $query = rtrim($query, ", ");
      $query .= " WHERE alias = :alias";
      $params['alias'] = $alias;
      
      $stmt = $db->prepare($query);
      $ret = $stmt->execute($params);
      if ($ret) {
        echo '{"status": "success","message": "User updated successfully"}';
      } else {
        http_response_code(400);
        echo 'Query failed.';
      }
  } catch (PDOException $e) {
      http_response_code(400);
      echo '{"status": "error","message":  " Update Target failed: ' . $e->getMessage() . '"}';
  }
  
}

function deleteUser($alias) {

  try {
      // Create a new database object
      $db = new Database();
      $db->connect();
      
      $query = "DELETE FROM target WHERE alias = :alias";
      

      $stmt = $db->prepare($query);
      $stmt->bindValue('alias', $alias);
      $stmt->execute();
      
      echo '{"status": "success","message": " Target deleted successfully"}';
  } catch (PDOException $e) {
      echo '{"status": "error","message": " Target deletion failed failed: ' . $e->getMessage() . '"}';
  }

}

function insertTest($num, $filename, $executable, $description, $local, $name, $technique_id, $args) {
  // Validate inputs
  if (strcasecmp($local, "TRUE") === 0) {
    $local = true;
  } else {
    $local = false;
  }
  if (empty($name)) {
    throw new InvalidArgumentException("Name cannot be empty");
  }
  if (empty($technique_id)) {
    throw new InvalidArgumentException("Technique ID cannot be empty");
  }
  
  try {
    $db = new Database();
    $db->connect();

    // Check if a test with the same technique_id and test_number already exists
    $query = "SELECT COUNT(*) AS count FROM tests WHERE technique_id = :technique_id AND test_number = :test_number";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':technique_id', $technique_id);
    $stmt->bindParam(':test_number', $num);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];

    if ($count > 0) {
      // If the test already exists, update it
      $query = "UPDATE tests SET 
                file_name = :file_name, 
                executable = :executable, 
                description = :description, 
                local_execution = :local_execution, 
                arguments = :arguments 
                WHERE technique_id = :technique_id AND test_number = :test_number";
    } else {
      // If the test doesn't exist, insert it
      $query = "INSERT INTO tests (technique_id, test_number, name, executable, local_execution, description, file_name, arguments) 
                VALUES (:technique_id, :test_number, :name, :executable, :local_execution, :description, :file_name, :arguments)";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':technique_id', $technique_id);
    $stmt->bindParam(':test_number', $num);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':executable', $executable);
    $stmt->bindParam(':local_execution', $local, PDO::PARAM_BOOL);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':file_name', $filename);
    $stmt->bindParam(':arguments', $args, PDO::PARAM_BOOL);
    
    $result = $stmt->execute();

    if ($result) {
      return true;
    } else {
      return false;
    }
  } catch (PDOException $e) {
    // Log error message
    error_log("Error inserting or updating test into database: " . $e->getMessage());
    return false;
  }
}
function getAnsibleOutput($file_id) {
  $file_path = ANSIBLE_OUTPUT_PATH . $file_id;
  // Check if the output file exists
  if (!file_exists($file_path)) {
    return json_encode(['end' => false, 'output' => 'Output file not found.']);
  }
  
  // Read the output file contents
  $output = file_get_contents($file_path);
  
  // Check if the output contains the end marker
  $end = strpos($output, 'PLAY RECAP') !== false;
  // Construct the response object
  $response = ['end' => $end, 'output' => $output];
  
  // Return the response as JSON
  return json_encode($response);
}



function saveTest($json_data){
  // Create a new database object
  $db = new Database();
  $db->connect();

  // Sanitize and validate input parameters
  $url = filter_var($json_data->url, FILTER_SANITIZE_URL);
  $filename = filter_var($json_data->file_name, FILTER_SANITIZE_STRING);
  $executable = filter_var($json_data->executable, FILTER_SANITIZE_STRING);
  $description = filter_var($json_data->description, FILTER_SANITIZE_STRING);
  $local = filter_var($json_data->local_execution, FILTER_VALIDATE_BOOLEAN);
  $name = filter_var($json_data->name, FILTER_SANITIZE_STRING);
  $id = $json_data->technique_id;
  $args = $json_data->args;

  $local = $local ? 1 : 0;
  $args = $args ? 1 : 0;

  // Check if ID is valid
  if(!isID($id)){
    http_response_code(400);
    echo json_encode(array("error" => "Invalid ID"));
    exit;
  } else {
    $query = "SELECT COUNT(*) > 0 AS exists FROM mitre WHERE id = '$id'";
    
    $result = executeQuery($query);
    $exists = json_decode($result, true);
    if(!$exists){
      http_response_code(400);
      echo json_encode(array("error" => "ID does not match any technique ID"));
      exit;
    }
  }

  // Validate URL
  if(!filter_var($url, FILTER_VALIDATE_URL)){
    http_response_code(400);
    echo json_encode(array("error" => "Invalid URL"));
    exit;
  }

  // Get the maximum test number
  $query = "SELECT MAX(test_number) FROM tests WHERE technique_id = '$id'";
  $result = executeQuery($query);
  $data = json_decode($result, true);
  $max = (int) $data[0]['max'];

  // Calculate the new test number
  if($max == null){
    $num = 1;
  } else {
    $num = $max + 1;
  }
  // Execute custom test script
  $command = "../engine/custom_test.sh -i ".$id." -u ".$url." -n ".$num; 

  // Execute the command with parameters
  $output = shell_exec($command);

  // Insert the test into the database
  $query = "INSERT INTO tests (test_number, file_name, executable, description, local_execution, name, technique_id, arguments) 
            VALUES (:num, :filename, :executable, :description, :local, :name, :id, :args)";
  $params = array(
    ':num' => $num,
    ':filename' => $filename,
    ':executable' => $executable,
    ':description' => $description,
    ':local' => $local,
    ':name' => $name,
    ':id' => $id,
    ':args' => $args
  );
  
  // Execute the prepared statement
  $stmt = $db->prepare($query);
  $result = $stmt->execute($params);

  // Check the result and send appropriate response
  if($result){
    http_response_code(200);
    echo json_encode(array("message" => "Test saving was successful"));
  } else {
    http_response_code(500);
    echo json_encode(array("error" => "Test saving failed"));
  }
}

function editTest($json_data){

  // Create a new database object
  $db = new Database();
  $db->connect();

  // Sanitize and validate input parameters
  $filename = filter_var($json_data->file_name, FILTER_SANITIZE_STRING);
  $executable = filter_var($json_data->executable, FILTER_SANITIZE_STRING);
  $test_number = filter_var($json_data->test_number, FILTER_SANITIZE_NUMBER_INT);
  $description = filter_var($json_data->description, FILTER_SANITIZE_STRING);
  $local = filter_var($json_data->local_execution, FILTER_VALIDATE_BOOLEAN);
  $name = filter_var($json_data->name, FILTER_SANITIZE_STRING);
  $id = $json_data->technique_id;
  $args = $json_data->args;

  $local = $local ? 1 : 0;
  $args = $args ? 1 : 0;
  // Check if ID is valid
  if(!isID($id)){
    http_response_code(400);
    echo json_encode(array("error" => "Invalid ID"));
    exit;
  }

  // Insert or update the test into the database
  $query = "UPDATE tests 
            SET file_name = :filename, executable = :executable, description = :description, local_execution = :local, 
            name = :name, arguments = :args
            WHERE technique_id = :id AND test_number = :test_number";
  $params = array(
    ':filename' => $filename,
    ':executable' => $executable,
    ':description' => $description,
    ':local' => $local,
    ':name' => $name,
    ':args' => $args,
    ':id' => $id,
    'test_number' => $test_number
  );

  // Execute the prepared statement
  $stmt = $db->prepare($query);
  $result = $stmt->execute($params);

  // Check the result and send appropriate response
  if($result){
    http_response_code(200);
    echo json_encode(array("message" => "Test edit was successful"));
  } else {
    http_response_code(500);
    echo json_encode(array("error" => "Test edit failed"));
  }
}

function deleteTest($tech, $test){
  try {
    // Create a new database object
    $db = new Database();
    $db->connect();
      
    // Prepare the delete statement with placeholders
    $sql = "DELETE FROM tests WHERE technique_id = :technique_id AND test_number = :test_number";

    $stmt = $db->prepare($sql);
    $params = array(
      ':technique_id' => $tech,
      ':test_number' => $test
    );
    echo "tech $tech test: $test";
    // Execute the statement
    $stmt->execute($params);

    // Check if any rows were affected
    if ($stmt->rowCount() > 0) {
      // Deletion successful, now delete the corresponding folder.
      // Very basic sanitization, see: https://stackoverflow.com/questions/1911382/sanitize-file-path-in-php
      $techSafe = basename($tech);
      $testSafe = basename($test);
      $folder_path = "../engine/customs/$techSafe/$testSafe"; 
      $output = shell_exec("rm -rf $folder_path");
      return true;
    } else {
      http_response_code(404); // Not found
      return false; // No rows were affected, test not found
    }

    $db = null; // Close connection
  } catch (PDOException $e) {
    // Log error message
    error_log("Error deleting test from database: " . $e->getMessage());
    http_response_code(500); // Server error
    return false;
  }
}

function getTechniques() {
  $query = "SELECT id, status FROM mitre WHERE id LIKE '%.%';";
  $subTechniques = json_decode(executeQuery($query));
  $query = "SELECT id, name, tactics, status FROM mitre WHERE id NOT LIKE '%.%';";
  $techniques = json_decode(executeQuery($query));

  $rankToStr = ['detected', 'executed', 'available', 'not available', ''];
  $strToRank = [];
  for ($i = 0; $i < count($rankToStr); $i++) {
    $strToRank[$rankToStr[$i]] = $i;
  }


  foreach($subTechniques as $subTech) {
    $techId = explode(".", $subTech->id)[0];

    foreach($techniques as $tech) {
      if ($techId != $tech->id) {
        continue;
      }
      $subTechRank = $strToRank[$subTech->status];
      $techRank = $strToRank[$tech->status];
      if ($subTechRank < $techRank) {
        $tech->status = $rankToStr[$subTechRank];
      }
    }
  }
  return json_encode($techniques);
}

########################################################
###     Main loop

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'startpage':
      // $query = "SELECT id, name, tactics, status FROM mitre WHERE id NOT LIKE '%.%';";
      // $result = executeQuery($query);
      echo getTechniques();
      break;

    case 'specific':
      if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if(isID($id)){
          $query = "SELECT id, name, description, url, tactics, platforms        
                    FROM mitre
                    WHERE id LIKE '$id' OR id LIKE '$id.%'
                    ORDER BY id;";
          
          $result = executeQuery($query);
          echo $result;
        } else {
          echo "ID: " . $id ." is not valid ID";
        }
        
      }
      break;
    case 'test_by_id':
      if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if(isID($id)){
          $query = "SELECT technique_id, test_number, name, arguments, local_execution FROM tests WHERE technique_id = '$id' ORDER BY test_number;";
          $result = executeQuery($query);
          echo $result;
        } else {
          http_response_code(404);
          echo "ID: " . $id ." is not valid ID";
        }
      }
    break;
    case 'targets':
      $query = "SELECT alias, ip FROM target";
      $result = executeQuery($query);
      echo $result; 
      break;
    case 'target_detail':
      if(isset($_GET['alias'])){
        $alias = $_GET['alias'];
        $query = "SELECT IP, sudo_user, alias, platform FROM target WHERE alias = '$alias'";
        $result = executeQuery($query);
        echo $result; 
      
        
      }
      break;
    case 'history':
      $target = null;
      $id = $_GET['id'];
      if(isID($id)){
        $id = explode(".", $id)[0];
        $didSucceed = false;
        $result = executeQuery("SELECT * FROM history WHERE test_id LIKE '$id%' ORDER BY start_time DESC;", $didSucceed);
        if (!$didSucceed) {
          http_response_code(500);
        }
        echo $result;
      } else {
        http_response_code(400);
        echo "ID: " . $id ." is not valid ID";
      }
      break;
    case 'get_pub_ssh_key':
      echo getenv('WEB_PUBLIC_KEY');
      break;
    case 'result':
      $id = $_GET['outputFileId'];
      if (!isset($id)) {
        http_response_code(400);
        echo '`outputFileId` parameter must be set';
      } else {
        echo getAnsibleOutput($id);
      }
      break;
    case 'get_custom_tests':
      echo executeQuery("SELECT * FROM tests;");
      break;
    case 'get_custom_ids':
      echo executeQuery("SELECT id AS technique_id, name FROM mitre WHERE id IN (SELECT technique_id FROM (SELECT DISTINCT ON (technique_id) technique_id FROM tests ORDER BY technique_id) AS distinct_tests) ORDER BY technique_id;");
      break;

  }
  
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // retrieve the data sent with the request
  $request_body = file_get_contents('php://input');
  $json_data = json_decode($request_body);
  if (isset($json_data->action)) {
    switch($json_data->action){
      case 'history':
        saveHistory($json_data);

        $status = ($json_data->detected) ? 'detected' : 'executed';
        $techId = explode('-', $json_data->testId)[0];
        // Do not overwrite detected status.
        $query="UPDATE mitre SET status = '$status' WHERE id = '$techId' AND status != 'detected';";
        $didSucceed = false;
        executeQuery($query, $didSucceed);  
        if (!$didSucceed) {
          http_response_code(500);
        }       
        break;
      case 'edit_target':
        $ip = $json_data->ip;
        $username = $json_data->username;
        $password = $json_data->password;
        $platform = $json_data->platform;
        $alias = $json_data->alias;            
        editUser($ip, $username, $password, $platform, $alias);
        break;
      case 'create_target':              
        // Get user data from POST request
        $ip = $json_data->ip;
        $username = $json_data->username;
        $password = $json_data->password;
        $alias = $json_data->alias;
        $platform = $json_data->platform;
        $query = "INSERT INTO target(IP, sudo_user, password, alias, platform) VALUES ('$ip', '$username', '$password', '$alias', '$platform')";
        $didSucceed = NULL;
        $result = executeQuery($query, $didSucceed);
        if (!$didSucceed) {
          http_response_code(400);
        }
        echo $result;
        break;
      case 'test':
        saveTest($json_data);
        break;
      case 'edit_test':
        editTest($json_data);
        break;
      default:
        echo "Unknown action";
        break;
    }  
  } else {
    echo "Action parameter is missing";
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  // retrieve the data sent with the request
  $request_body = file_get_contents('php://input');
  $json_data = json_decode($request_body);
  if ($json_data && isset($json_data->action)) {
    switch($json_data->action){
      case 'target':
        if (isset($json_data->alias)) {
          $alias = (string) $json_data->alias;
          deleteUser($alias);
        } else {
          echo "Alias parameter is missing";
        }
        break;
      case 'test':
        if(isset($json_data->technique_id) && isset($json_data->test_number)){
          deleteTest($json_data->technique_id, $json_data->test_number);
        }
        break;
      default:
        echo "Unknown action";
        break;
    }
  } else {
    echo "Action parameter is missing";
  }
}

?>