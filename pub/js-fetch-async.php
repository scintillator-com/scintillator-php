async () => {
	try{
		<?=$init;?>

		const res = await fetch( <?=$url;?>, init )
		const text = await res.text()
		console.log( text )

		return text
	}
	catch( error ){
		console.error( error )
	}
}