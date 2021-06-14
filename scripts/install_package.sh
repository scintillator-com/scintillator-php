#!/bin/bash

echo $1
PACKAGE_PATH="${1}"

echo $2
TARGET_PATH="${2}"

TMP_PATH="/tmp/scintillator-php"
if [ -d "${TMP_PATH}" ]; then
  rm -rf "${TMP_PATH}"

mkdir -p "${TMP_PATH}
tar -zxvf "${PACKAGE_PATH}" -C "${TMP_PATH}"

dirs=$(ls "${TMP_PATH}")
for src in $dirs; do
  if [ "${src}" == "pub" ]; then
    dest="html"
    
    echo "Updating /usr/share/nginx/${dir}/"
    rsync -chmrvz --del "${TMP_PATH}/${src}/api/" "${TARGET_PATH}/${dest}/api/"
    rsync -chmvz "${TMP_PATH}/${src}/" "${TARGET_PATH}/${dest}/"
  else
    dest="${src}"

    echo "Updating /usr/share/nginx/${dir}/"
    rsync -chmrvz --del "${TMP_PATH}/${src}/" "${TARGET_PATH}/${dest}/"
  fi

  echo "Securing /usr/share/nginx/${dest}/"
  sudo chown -R nginx:nginx "${TARGET_DIR}/${dest}/"
  sudo chmod -R ug=rX "${TARGET_DIR}/${dest}/"
  sudo chmod -R o=-rwx "${TARGET_DIR}/${dest}/"
done

