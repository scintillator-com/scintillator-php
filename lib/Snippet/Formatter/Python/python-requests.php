try:
    r = requests.<?=$method;?>( '<?=$url;?>',  )
    r.raise_for_status()
    return r.text
except ConnectionError as ex:
		logging.exception( ex )
except HTTPError as ex:
	  #bad status code >= 400
		logging.exception( ex )
