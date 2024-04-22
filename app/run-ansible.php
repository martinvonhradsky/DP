<?php
header("Access-Control-Allow-Origin: *");

// Define constants for configuration parameters
define('ANSIBLE_PLAYBOOK_PATH', '../engine/');
define('ANSIBLE_HOSTS_PATH', '/etc/ansible/hosts');

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
  $hostsContent .= "\n";
  
  // Write the content to the hosts file
  echo $hostsContent;
  file_put_contents(ANSIBLE_HOSTS_PATH, $hostsContent);
  return 0;
}

// Setup target
function setupTarget($alias) {
  if (setAnsibleHosts($alias) != 0) {
    return;
  }
  // Concurrent requests will be handled by different PHP processes so the environment
  // variables will not get overwritten.
  exec('echo popici > output.txt');

  // Run the Ansible playbook for target setup. See: https://stackoverflow.com/a/29456196/4500196 
  $command = "ansible-playbook --limit=$alias ".ANSIBLE_PLAYBOOK_PATH."target_setup.yaml";
  executeAnsible($command, "");
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
      echo "local exe";

      if($alias == "null") {
        // Set the HTTP response code to 400
        http_response_code(400);
        // Return the error message
        echo "Test execution failed. No target device specified for local execution.";

      }

      // local execution that means its executed locally on remote machine
      $command = "ansible-playbook --limit=$alias " . ANSIBLE_PLAYBOOK_PATH . "execute_custom_test.yaml --extra-vars '{\"executable\":\"{$result[0]['executable']}\", \"test_file\":\"{$result[0]['file_name']}\",\"directory\":\"./customs/{$test_array[0]}\", \"test_number\":\"{$test_array[1]}\", \"args\":\"{$args}\", \"tech_id\":\"{$test_array[0]}\", \"alias\":\"{$alias}\"}'";
        
      
    }else{
      echo "remote exe";
      // remote execution that means its executed from ansible server to remote machine
      $command = "ansible-playbook " . ANSIBLE_PLAYBOOK_PATH . "execute_custom_test_remote.yaml --extra-vars '{\"executable\":\"{$result[0]['executable']}\", \"test_file\":\"{$result[0]['file_name']}\", \"test_number\":\"{$test_array[1]}\", \"args\":\"{$args}\", \"tech_id\":\"{$test_array[0]}\", \"local_execution\":false}'";
      echo $command;            
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
    $command = "ansible-playbook --limit=$alias ".ANSIBLE_PLAYBOOK_PATH."execute_test.yaml --extra-vars '{\"test\":\"".$test."\"}'";
  }
  
  executeAnsible($command, $metadata);
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

function executeAnsible($command, $metadata) {
  // Check if the output.txt file exists, and delete it if it does
  if (file_exists('output.txt')) {
    unlink('output.txt');
  }
  
  // Create the output.txt file and write the $metadata variable to it
  $file = fopen('output.txt', 'w');
  fwrite($file, json_encode($metadata));
  fclose($file);
  
  // Append the [start] string to the output.txt file
  $file = fopen('output.txt', 'a');
  fwrite($file, "[start]\n");
  fclose($file);
  
  // Open a process to execute the command and append its output to the output.txt file
  $command .= " >> output.txt 2>&1 &";
  exec($command);

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
