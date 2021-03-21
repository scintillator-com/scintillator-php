try{
	<?=$config;?>

	return <?=$line;?>
		.then( res => {
			console.log( res.data )
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