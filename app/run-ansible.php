<?php
header("Access-Control-Allow-Origin: *");

// Define constants for configuration parameters
define('ANSIBLE_PLAYBOOK_PATH', '../engine/');
define('ANSIBLE_HOSTS_PATH', '/etc/ansible/hosts');

require 'api.php';

// Set target host for Ansible
function setTarget($ip, $user, $pass) {
  // Build the content of the hosts file for Ansible
  $hostsContent = "[target]\n".$ip." ansible_connection=ssh ansible_ssh_user=".$user." ansible_ssh_pass=".$pass."\n";
  // Write the content to the hosts file
  file_put_contents(ANSIBLE_HOSTS_PATH, $hostsContent);
}

// Setup target
function setupTarget($ip, $user, $pass) {
  file_put_contents("./output.txt", "jooojha");
  // php set env variable.
  exec("sshpass -p \"$NAHODNE_MENO\" ssh-copy-id -o StrictHostKeyChecking=no centos@192.168.100.93 >> output.txt 2>&1");

/*
  // set user to /etc/ansible/hosts
  setTarget($ip, $user, $pass);
  // Run the Ansible playbook for target setup 
  $command = "ansible-playbook ".ANSIBLE_PLAYBOOK_PATH."target_setup.yaml";
  executeAnsible($command, "");
  */
}

function executeAnsibleTest($alias, $test) {
  // get Target data
  $query = "SELECT ip, alias, sudo_user, encode(password, 'escape')::text as password, platform FROM target WHERE alias='" . $alias . "' ;";
  $result=json_decode(executeQuery($query), true);
  if ($result == NULL){
    echo "Error: No such host as: " . $alias;   
  }
  // set Target to /etc/ansible/hosts
  setTarget($result[0]['ip'], $result[0]['sudo_user'], $result[0]['password']);
  
  # create test metadata json
  $metadata = '{"test_id":"' . $test .'", "target":"'. $alias . '"}';
  $test_array = explode("-", $test);
  $query = "SELECT executable, file_name  FROM tests WHERE technique_id='$test_array[0]' AND test_number='$test_array[1]';";
  $result=json_decode(executeQuery($query), true);
  
  if($result[0]['executable'] !== "Invoke atomic"){
    $path = "customs/".$test_array[0]."/".$test_array[1];
    // Run the Ansible playbook for custom test execution
    $command = "ansible-playbook ".ANSIBLE_PLAYBOOK_PATH."execute_custom_test.yaml --extra-vars '{\"executable\":\"".$result[0]['executable']."\", \"test_file\":\"".$result[0]['file_name']."\",\"directory\":\"".$path."\",\"test_number\":\"".$test."\"}'";
  }else{
    // Run the Ansible playbook for InvokeAtomic for test execution with the specified test ID
    $command = "ansible-playbook ".ANSIBLE_PLAYBOOK_PATH."execute_test.yaml --extra-vars '{\"test\":\"".$test."\"}'";
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
    case "setTarget":
      // Set the target host for Ansible with the provided IP address, username, and password
      setTarget($_GET["ip"], $_GET["user"], $_GET["pass"]);
      break;
    case "setupTarget":
      if (isset($_GET['ip']) && isset($_GET['user']) && isset($_GET['pass'])){        
        setupTarget($_GET["ip"], $_GET["user"], $_GET["pass"]);
        break;        
      }
      break;
    case "executeTest":
      if(isset($_GET["alias"]) && isset($_GET['id']))
      $alias = $_GET["alias"];
      $test_to_execute = $_GET['id'];
      executeAnsibleTest($alias, $test_to_execute);      
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
