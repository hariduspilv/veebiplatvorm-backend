#!/bin/bash

declare -a SITES

HOME=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
SITE_DIR=${HOME}'/web/sites'
echo "Home set to: ${SITE_DIR}"
cd "${SITE_DIR}"

# Loop:
for SITE in $(ls -d */ | cut -f1 -d'/'); do
  # Skip default site.
  if [ ! "${SITE}" == "default" ]; then
    SITES+=( ${SITE} )
  fi
done

usage() {
    echo "Usage: $0 [option]

Wrapper script for multisite update operations

General operations (safe to execute on-the-fly):
     -h  show this help text

Application operations:
     -U  update server to latest version
     -S  show sites list
     -L  update local Lando to latest version" 1>&2; exit 1;
}

updateApp(){
     echo "Home set to: ${HOME}"
     cd "${HOME}"
     echo "Git pull start at $(timestamp)"
     git pull
     echo "Composer install start at $(timestamp)"
     composer install
     #echo "Default update database start at $(timestamp)"
     #vendor/drush/drush/drush -y updb
     #echo "Default configuration sync start at $(timestamp)"
     #vendor/drush/drush/drush -y cim
     #echo "Default cache rebuild start at $(timestamp)"
     #vendor/drush/drush/drush cr

     for I in "${SITES[@]}"; do
        echo "${I} update database start at $(timestamp)"
        vendor/drush/drush/drush -l ${I} -y updb
        echo "${I} configuration sync start at $(timestamp)"
        vendor/drush/drush/drush -l ${I} -y cim
        echo "${I} cache rebuild start at $(timestamp)"
        vendor/drush/drush/drush -l ${I} cr
     done
}

updateLandoApp(){
     cd "${HOME}"
     echo "Home set to: ${HOME}"
     #echo "Composer install start at $(timestamp)"
     #lando composer install
     echo "Default update database start at $(timestamp)"
     lando drush -y updb
     echo "Default configuration sync start at $(timestamp)"
     lando drush cim
     echo "Default cache rebuild start at $(timestamp)"
     lando drush cr

     for I in "${SITES[@]}"; do
        echo "${I} update database start at $(timestamp)"
        lando drush -l ${I} -y updb
        echo "${I} configuration sync start at $(timestamp)"
        lando drush -l ${I} -Y cim
        echo "${I} cache rebuild start at $(timestamp)"
        lando drush -l ${I} cr
     done
}

showSites(){
     N=1
     for I in "${SITES[@]}"; do
      echo "${N}) ${I}"
      ((N=N+1))
     done
}
# Define a timestamp function
timestamp() {
  date +"%d.%m.%Y %H:%M:%S"
}
while getopts ":hLSUW" O; do
    case "${O}" in
        h)
            usage
            ;;
        U)
            updateApp
            ;;
        L)
          updateLandoApp
          ;;
        S)
          showSites
          ;;
        *)
            usage
            ;;
    esac
done
shift $((OPTIND-1))
