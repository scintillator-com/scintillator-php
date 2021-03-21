
echo "=== Configuration Report ==="
echo "= IS_DEV: [${IS_DEV}]"
echo "= MONGO_DB: [${MONGO_DB}]"
echo "= MONGO_URI: [${MONGO_URI}]"
echo "= SESSION_LIMITS: [${SESSION_LIMITS}]"
echo "= OVERRIDE_PATH: [${OVERRIDE_PATH}]"
echo "============================"

function upsert(){
  local key=\$1
  local value=\$2
  local path=\$3

  grep -Fq "Environment=\"\${key}=" "\${path}"
  if [ $? -eq 0 ]; then
    sed -i "s/^Environment=\"\${key}=.*/Environment=\"\${key}=\${value}\"/" "\${path}"
  else
    echo "Environment=\"${key}=${value}\"" | tee -a "${path}"
  fi
}

if [ !-f "${OVERRIDE_PATH}" ]; then
  sudo sh -c "cat <<EOF >${OVERRIDE_PATH}
[Service]
EOF"
fi

sudo upsert IS_DEV $IS_DEV $OVERRIDE_PATH
sudo upsert MONGO_DB $MONGO_DB $OVERRIDE_PATH
sudo upsert MONGO_URI $MONGO_URI $OVERRIDE_PATH
sudo upsert SESSION_LIMITS $SESSION_LIMITS $OVERRIDE_PATH

sudo systemctl daemon-reload
sudo systemctl restart php-fpm.service
