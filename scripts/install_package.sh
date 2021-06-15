#!/bin/bash

if [[ $# -ne 2 ]]; then
  echo "Argument quantity must be 2, received $#"
  exit 1
fi

if [[ ! -f $1 ]]; then
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

mkdir -p "${TMP_PATH}"

# -z: enable gzip
# -x: extract
# -v: verbose
# -f: use file -f=file.tar.tz
tar -zxf "${PACKAGE_PATH}" -C "${TMP_PATH}"
rm "${PACKAGE_PATH}"

echo "Unlocking ${TARGET_PATH}"
user=$(id -un)
sudo chown -R "${user}" "${TARGET_PATH}"

dirs=$(ls "${TMP_PATH}")
for src in $dirs; do
  if [ "${src}" == "pub" ]; then
    dest="html"

    if [ ! -d "${TARGET_PATH}/${dest}/api/" ]; then
        mkdir -p "${TARGET_PATH}/${dest}/api/"
    fi

    echo "Updating ${TARGET_PATH}/${dest}/api/"
    rsync -chmr --del "${TMP_PATH}/${src}/api/" "${TARGET_PATH}/${dest}/api/"

    echo "Updating ${TARGET_PATH}/${dest}/"
    rsync -ch ${TMP_PATH}/${src}/* "${TARGET_PATH}/${dest}/"
  else
    dest="${src}"

    # -c: checksum
    # -h: human readable numbers
    # -m: prune empty directories
    # -n: dry-run
    # -r: recursive
    # -v: verbose
    # -z: compress

    echo "Updating ${TARGET_PATH}/${dest}/"
    rsync -chmr --del "${TMP_PATH}/${src}/" "${TARGET_PATH}/${dest}/"
  fi

done

#re-lock
echo "Securing ${TARGET_PATH}"
sudo chown -R nginx:nginx "${TARGET_PATH}"
sudo chmod -R ug=rX       "${TARGET_PATH}"
sudo chmod -R o=-rwx      "${TARGET_PATH}"

