
 
//JQuery Ajax GET
 
$.ajax({
        url: `https://jsonplaceholder.typicode.com/todos/1?name=saurav`,
        type: 'GET',
        dataType: 'json', 
        success: function(response) {
            console.log(response);
        
        }
         error: function (error) {
           console.log(error)
    }
    });
 
 
//JQuery Ajax POST
$.ajax({
        url: `https://jsonplaceholder.typicode.com/todos/1?name=saurav`,
        type: 'POST',
        dataType: 'json', 
        data: { name: 'Saurav' },
        success: function(response) {
            console.log(response);
        
        }
         error: function (error) {
           console.log(error)
    }
    });
 
 
 
//XMLHttpRequest GET
 
let xhr = new XMLHttpRequest();
xhr.onreadystatechange = function() {
    if (xhr.readyState == XMLHttpRequest.DONE) {
        console.log(xhr.responseText);
    }
}
xhr.open('GET', `https://jsonplaceholder.typicode.com/todos/1?name=saurav`, true);
xhr.send(null);
 
 
//XMLHttpRequest POST
let xhr = new XMLHttpRequest();
xhr.onreadystatechange = function() {
    if (xhr.readyState == XMLHttpRequest.DONE) {
      
        console.log(xhr.responseText);
    }
}
xhr.open('POST', `https://jsonplaceholder.typicode.com/todos/1?name=saurav`, true);
xhr.send('name=saurav');









<?php
// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "Cookie: foo=bar\r\n"
    'content' => $body,
  )
);

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$file = file_get_contents('http://www.example.com/', false, $context);
?>