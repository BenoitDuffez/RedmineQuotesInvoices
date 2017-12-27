#!/bin/bash

TAG_FORMAT='^v?\d+\.\d+\.\d+$'
DRY_RUN=1
NORMAL="$(tput sgr0)"
BOLD="$(tput bold)"
DEVELOP=0

function say_red {
    echo -e "${BOLD}$(tput setaf 1)$@${NORMAL}"
}

function say_green {
    echo -e "${BOLD}$(tput setaf 2)$@${NORMAL}"
}

function say_yellow {
    echo -e "$(tput setaf 3)>>> $@${NORMAL}"
}

function say_bold {
    echo -e "${BOLD}$@${NORMAL}"
}

function usage {
    {
        [ "$1" != "" ] && say_red "Error: $@" && echo
        say_red "Usage: $0 [-f]"
        say_red " -f: force (default is dry run)"
        say_red " -d: deploy on develop (not the latest tag)"
    } >&2
    exit 1
}

# Show the command line with an optional dry run message if applicable
function say {
    if [ "${DRY_RUN}" == "1" ]; then
        say_green "[dry run] $@"
    else
        say_yellow "$@"
    fi
}

# Show command
function say_cmd {
    say_bold "\$ $@"
}

# Show command + execute
function say_exec {
    say_cmd "$@"
    $@ | while read line; do
        say "${line}"
    done
}

# Show command + execute only if not in dry run
function safe_exec {
    if [ "${DRY_RUN}" == "1" ]; then
        say_cmd "$@"
    else
        say_exec "$@"
    fi
}

# Abort script and show error
function fail {
    say_red
    say_red "Error: Step $1 failed"
    exit 1
}

# ---------------------------------------
# Start script
# ---------------------------------------

# Parse CLI options
while getopts "fd" opt; do
  case ${opt} in
    f)  DRY_RUN=0;;
    d)  DEVELOP=1;;
    \?) usage "Invalid option: -$OPTARG"
      ;;
  esac
done

if [ "${DRY_RUN}" == "1" ]; then
    say_green "Dry run. Use -f to actually do the operations"
    echo "----------------------------"
fi

# Git
say_exec git fetch
if [ "${DEVELOP}" == "0" ]; then
    lastTag=$(git tag --list | grep -P ${TAG_FORMAT} | sort -t. -k 1.2,1n -k 2,2n -k 3,3n | tail -1)
    say_green "About to deploy $lastTag"
else
    lastTag=develop
    say_green "About to deploy from develop"
fi

# Checkout latest files
if [ "${DEVELOP}" == "0" ]; then
    say_exec git checkout ${lastTag} || fail "git checkout"
    say_exec git reset --hard ${lastTag} || fail "git reset"
else
    say_exec git pull || fail "git checkout"
    say_exec git reset --hard ${lastTag} || fail "git reset"
fi

# Check requirements
reqFail=1
first=1
say_exec php bin/symfony_requirements | ( while read line; do
    [ "${first}" == "1" ] && say_bold "${line}" || echo "${line}"
    first=0
    grep -q "Your system is ready to run Symfony projects"<<<"${line}" && reqFail=0
done
exit ${reqFail})
[ $? != 0 ] && fail "Symfony requirements"

# Composer
composerCmd="composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader"
if [ "${DRY_RUN}" == "1" ]; then
    say_exec ${composerCmd} --dry-run
else
    safe_exec ${composerCmd} || fail "Composer"
fi

# Database
if [ "${DRY_RUN}" == "1" ]; then
    say_exec php bin/console doctrine:schema:update --dump-sql
else
    safe_exec php bin/console doctrine:schema:update --force
fi

# Cache
safe_exec php bin/console cache:clear --env=prod --no-debug --no-warmup || fail "Clear cache"
safe_exec php bin/console cache:warmup --env=prod || fail "Cache warmup"

echo "----------------------------"
say_green "Done"

