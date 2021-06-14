#!/bin/bash

if [[ $# -ne 2 ]]; then
  echo "Argument quantity must be 2, received $#"
  exit 1
fi


if [[ ! -d $1 ]]; then
  echo "Bad PACKAGE_PATH: $1"
  exit 1
fi

if [[ ! -d $2 ]]; then
  echo "Bad TARGET_PATH: $2"
  exit 1
fi

PACKAGE_PATH="${1}"
TARGET_PATH="${2}"
TMP_PATH="/tmp/scintillator-php"
if [ -d "${TMP_PATH}" ]; then
  rm -rf "${TMP_PATH}"
fi

echo "9"
mkdir -p "${TMP_PATH}"
tar -zxvf "${PACKAGE_PATH}" -C "${TMP_PATH}"

echo "13"
dirs=$(ls "${TMP_PATH}")
for src in $dirs; do
  echo "${src}"
  if [ "${src}" == "pub" ]; then
    dest="html"
    
    echo "Updating ${TARGET_PATH}/${dest}/api/"
    rsync -chmrvz --del "${TMP_PATH}/${src}/api/" "${TARGET_PATH}/${dest}/api/"

    echo "Updating ${TARGET_PATH}/${dest}/"
    rsync -chmvz "${TMP_PATH}/${src}/" "${TARGET_PATH}/${dest}/"
  else
    dest="${src}"

    echo "Updating ${TARGTE_PATH}/${dest}/"
    rsync -chmrvz --del "${TMP_PATH}/${src}/" "${TARGET_PATH}/${dest}/"
  fi

  echo "Securing /usr/share/nginx/${dest}/"
  sudo chown -R nginx:nginx "${TARGET_PATH}/${dest}/"
  sudo chmod -R ug=rX "${TARGET_PATH}/${dest}/"
  sudo chmod -R o=-rwx "${TARGET_PATH}/${dest}/"
done

