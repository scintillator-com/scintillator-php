async () => {
	try{
		<?=$config;?>

		const res = await <?=$line;?>
		console.log( res.data )

		return res.data
	}
	catch( error ){
		console.error( error )
	}
}