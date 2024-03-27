#!/bin/bash

# fetch arguments - technique id - url to download code
while getopts "i:u:n:f:g:" flag
do
  case "${flag}" in
    i) id=${OPTARG};;
    u) url=${OPTARG};;
    n) test_num=${OPTARG};;
    f) file_name=${OPTARG};;
    g) git==${OPTARG};;
  esac
done

output=$(ansible-playbook /var/www/html/module/engine/save_custom_test.yaml --extra-vars "{\"test_id\":\"${id}\", \"test_number\":\"${test_num}\",\"url\":\"${url}\",\"file_name\":\"${file_name}\",\"git\":\"${git}\"}")
echo "${output}"