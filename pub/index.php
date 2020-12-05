<!DOCTYPE html>
<html lang="en">
<head>
	<title></title>
	
	<meta http-equiv="Content-Language" content="en-us" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="description" content="">
	<meta name="keywords" content="" />

	<link media="all" rel="stylesheet" type="text/css" href="/css/core.css" />
	<link media="all" rel="stylesheet" type="text/css" href="/css/history.css" />

<style type="text/css">

</style>
</head>
<body>
<div id="page">
	<nav style="float: left; width: 200px;">
		<h1>LOGO</h1>
		<ul>
			<li>Dashboard</li>
			<li>Tools</li>
			<li>Settings</li>
		</ul>
	</nav>

	<main style="margin-left: 200px;">
		<table id="history" style="width: 100%;">
		<thead>
		<tr>
			<th>Date</th>
			<th>Method</th>
			<th>Host</th>
			<th>Path</th>
			<th>Request</th>
			<th>Response</th>
		</tr>
		</thead>
		<tbody>
		</tbody>
		</table>
		
		<div class="tac">
			<button id="more">More</button>
		</div>
	</main>
</div>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

const fetchHistory = async ( page, filters ) => {
	if( !page )
		page = 1

	let query = `?page=${page}&pageSize=25`
	if( filters && Object.keys( filters ).length ){
		for( let [ key, value ] of Object.entries( filters ) ){
			query += '&'+ encodeURIComponent( key ) +'='+ encodeURIComponent( value )
		}
	}
	
	const config = {
		'method': 'GET',
		'headers': {
			'Accept': 'application/json'
		}
	}

	let res = null, data = null
	try{
		res = await fetch( `/api/1.0/history${query}`, config )
		data = await res.json()
	}
	catch( err ){
		return
	}

	const tbody = document.getElementById( 'history' ).getElementsByTagName( 'tbody' )[0]
	tbody.innerHTML = ''
	
	let html, row, rows = []
	for( row of data ){
		html = `
			<tr>
				<td>${new Date(row.request.created)}</td>
				<td><span class="method-${row.request.method}">${row.request.method}</span></td>
				<td>${row.request.host}</td>
				<td>${row.request.path}</td>
				<td>
					Headers: ${row.request.headers.length}<br />
					Content-Type: ${row.request.content_type}
				</td>`

			if( row.response ){
				html += `
				<td>
				Status: <span class="status-${row.response.status_code}">${row.response.status_code}</span><br />
					Headers: ${row.response.headers.length}<br />
					Content-Type: ${row.response.content_type}<br />
					Body: <i>${row.response.body}</i>
				</td>`
			}
			else{
				html += `
				<td></td>`
			}

		html += `
			</tr>`
		
		rows.push( html )
	}
	
	tbody.innerHTML = rows.join( '\n' )
}

window.addEventListener( 'load', () => {
	fetchHistory()
})
//]]>
</script>
</body>
</html>