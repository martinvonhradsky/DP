<?php
header("Access-Control-Allow-Origin: *");

// Define constants for configuration parameters
define('ANSIBLE_PLAYBOOK_PATH', '../engine/');
define('ANSIBLE_HOSTS_DIR', '/etc/ansible/secmon_hosts');

require 'api.php';


// Set target host for Ansible.
// Return 0 on success, non-zero otherwise.
function setAnsibleHosts($alias) {
  // get Target data
  $query = "SELECT IP, password, sudo_user, alias, platform FROM target WHERE alias = '$alias'";
  $didSucceed = NULL;
  $result=executeQuery($query, $didSucceed);
  if ($result == NULL){
    http_response_code(400);
    echo "Error: No such host as: " . $alias;
    return 1;
  } else if (!$didSucceed) {
    http_response_code(500);
    echo "Internal error occurred: " . $result;
    return 1;
  }
  $result = json_decode($result, true);
  
  // Build the content of the hosts file for Ansible
  $hostsContent = "[" . $alias . "]\n" . $result[0]['ip'] ." ansible_connection=ssh ansible_ssh_user=" . $result[0]['sudo_user'];
  
  // Using password, not public/private keys.
  $pwd = $result[0]['password'];
  if ($pwd != "") {
    $hostsContent .= " ansible_ssh_pass=" . $pwd;
  }
  $hostsContent .= " ansible_sudo_pass=" . $pwd;

  $hostsContent .= "\n";
  
  if (!is_dir(ANSIBLE_HOSTS_DIR)) {
    if (!mkdir(ANSIBLE_HOSTS_DIR, 0755)) {
      http_response_code(500);
      echo "Error creating directory " . ANSIBLE_HOSTS_DIR;
      return 1;
    }
  }

  // Write the content to the hosts file
  file_put_contents(ANSIBLE_HOSTS_DIR . '/' . $alias, $hostsContent);
  return 0;
}

// Setup target
function setupTarget($alias) {
  if (setAnsibleHosts($alias) != 0) {
    return;
  }

  // Run the Ansible playbook for target setup. See: https://stackoverflow.com/a/29456196/4500196 
  $command = "ansible-playbook -i " . ANSIBLE_HOSTS_DIR . " --limit=$alias ".ANSIBLE_PLAYBOOK_PATH."target_setup.yaml";
  $output_file_id = executeAnsible($command, "");
  echo json_encode(['output_file_id' => $output_file_id]);;
}

function executeAnsibleTest($alias, $test, $args) {
  if($args == "null"){
    $args = "";
  }


  if($alias !== "null"){
    // get Target data
    $query = "SELECT ip, alias, sudo_user, password, platform FROM target WHERE alias='" . $alias . "' ;";
    $result=json_decode(executeQuery($query), true); 
    
    if ($result == NULL){
      echo "Error: No such host as: " . $alias;   
    }
    // set Target to /etc/ansible/hosts
    //setTarget($result[0]['ip'], $result[0]['sudo_user'], $result[0]['password']);
    setAnsibleHosts($alias);  
  }

  # create test metadata json
  $metadata = '{"test_id":"' . $test .'", "target":"'. $alias . '"}';
  $test_array = explode("-", $test);
  $query = "SELECT executable, file_name, arguments, local_execution FROM tests WHERE technique_id='$test_array[0]' AND test_number='$test_array[1]';";
  $result=json_decode(executeQuery($query), true);


  if($result[0]['executable'] !== "Invoke atomic"){
    
    if($result[0]['local_execution']){

      if($alias == "null") {
        // Set the HTTP response code to 400
        http_response_code(400);
        // Return the error message
        echo "Test execution failed. No target device specified for local execution.";
        return;
      }

      // local execution that means its executed locally on remote machine
      $command = "ansible-playbook -i " . ANSIBLE_HOSTS_DIR . " --limit=$alias " . ANSIBLE_PLAYBOOK_PATH . "execute_custom_test.yaml --extra-vars '{\"executable\":\"{$result[0]['executable']}\", \"test_file\":\"{$result[0]['file_name']}\",\"directory\":\"./customs/{$test_array[0]}\", \"test_number\":\"{$test_array[1]}\", \"args\":\"{$args}\", \"tech_id\":\"{$test_array[0]}\", \"alias\":\"{$alias}\"}'";
        
      
    }else{
      // remote execution that means its executed from ansible server to remote machine
      $command = "ansible-playbook -i " . ANSIBLE_HOSTS_DIR . " --limit=$alias " . ANSIBLE_PLAYBOOK_PATH . "execute_custom_test_remote.yaml --extra-vars '{\"executable\":\"{$result[0]['executable']}\", \"test_file\":\"{$result[0]['file_name']}\", \"test_number\":\"{$test_array[1]}\", \"args\":\"{$args}\", \"tech_id\":\"{$test_array[0]}\", \"local_execution\":false}'";
    }
    
    /*
    $path = "customs/".$test_array[0]."/".$test_array[1];
    // Run the Ansible playbook for custom test execution
    //old
    $command = "ansible-playbook --limit=$alias ".ANSIBLE_PLAYBOOK_PATH."execute_custom_test.yaml --extra-vars '{\"executable\":\"".$result[0]['executable']."\", \"test_file\":\"".$result[0]['file_name']."\",\"directory\":\"".$path."\",\"test_number\":\"".$test."\"}'";
    // new
    $command = "ansible-playbook --limit=$alias " . ANSIBLE_PLAYBOOK_PATH . "execute_custom_test.yaml --extra-vars '{\"executable\":\"{$result[0]['executable']}\", \"test_file\":\"{$result[0]['file_name']}\",\"directory\":\"./customs/{$test_array[0]}\", \"test_number\":\"{$test}\", \"args\":\"8.8.8.8\", \"tech_id\":\"{$test_array[0]}\", \"alias\":\"{$alias}\"}'";

  */}else{
    // Run the Ansible playbook for InvokeAtomic for test execution with the specified test ID
    $command = "ansible-playbook -i " . ANSIBLE_HOSTS_DIR . " --limit=$alias ".ANSIBLE_PLAYBOOK_PATH."execute_test.yaml --extra-vars '{\"test\":\"".$test."\"}'";
  }

  $output_file_id = executeAnsible($command, $metadata);
  echo json_encode(['output_file_id' => $output_file_id]);;
}

/*
function executeAnsible($command){
  // Tag start execution
  echo "[start]";
  // Open a process to execute the command and read its output
  $handle = popen($command, 'r');
  
  // Read the output from the process line by line and send it to the client
  while (!feof($handle)) {
    echo fgets($handle);
    ob_flush();
    flush();
  }
  
  // Close the process handle
  pclose($handle);
  echo "[end]";
}
*/

// Returns file_id of the output file.
function executeAnsible($command, $metadata) {
  $file_id = uniqid();
  $file_path = ANSIBLE_OUTPUT_PATH . $file_id;

  // Check if the path exists, and delete it if it does
  if (file_exists($file_path)) {
    unlink($file_path);
  }
  
  // Create the output file and write the $metadata variable to it
  $file = fopen($file_path, 'w');
  fwrite($file, json_encode($metadata));
  fclose($file);
  
  // Append the [start] string to the output file
  $file = fopen($file_path, 'a');
  fwrite($file, "[start]\n");
  fclose($file);
  
  // Open a process to execute the command and append its output to the output.txt file
  $command .= " >> " . $file_path . " 2>&1 &";
  exec($command);
  return $file_id;
}







// Handle incoming requests
if (isset($_GET["action"])) {
  switch ($_GET["action"]) {
    case "setupTarget":
      if (isset($_GET['alias'])){
        setupTarget($_GET["alias"]);
      } else {
        http_response_code(400);
        echo "The 'alias' was not set.";
      }
      break;
    case "executeTest":
      if(isset($_GET["alias"]) && isset($_GET['id']) && isset($_GET['args']))
      $alias = $_GET["alias"];
      $test_to_execute = $_GET['id'];
      $args = $_GET['args'];
      executeAnsibleTest($alias, $test_to_execute, $args);      
      break;
    default:
      // Invalid action
      http_response_code(400);
      exit;
  }
} else {
  // No action specified
  http_response_code(400);
  exit;
}

?>
