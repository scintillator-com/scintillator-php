try{
	<?=$init;?>

	return fetch( <?=$url;?>, init )
		.then( res => {
			return res.text()
		})
		.then( text => {
			console.log( text )
			return text
		})
		.catch( error => {
			//response error
			console.error( error )
		})
}
catch( error ){
	//config error
	console.error( error )
}