/POST Axios
 
//method 1
axios.post('https://jsonplaceholder.typicode.com/todos/1',{name:'saurav'})
    .then((response)=>{
        console.log(response)
    })
    .catch((e)=>{
        console.log(e)
    })
 
//method 2
axios({
  method: 'post',
  url: 'https://jsonplaceholder.typicode.com/todos/1',
  data: {
    name: 'Saurav',
  }
})
 .then((response)=>{
        console.log(response)
})
.catch((e)=>{
        console.log(e)
})
 
 
//GET Axios
 
//method 1
axios.get('https://jsonplaceholder.typicode.com/todos/1',{params:{name:'saurav'}})
    .then((response)=>{
        console.log(response)
    })
    .catch((e)=>{
        console.log(e)
    })
 
//GET Fetch
 
fetch(`https://jsonplaceholder.typicode.com/todos/1?name=saurav`)
    .then((response)=>{
        console.log(response.json())
    })
    .catch((e)=>{
        console.log(e)
    })
 
//POST Fetch
fetch(`https://jsonplaceholder.typicode.com/todos/1?name=saurav`, {
    method: 'post',
    body: JSON.stringify({name:'saurav'})
  })
    .then((response)=>{
        console.log(response.json())
    })
    .catch((e)=>{
        console.log(e)
    })
 
 
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