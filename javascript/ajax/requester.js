function loadRequester(file_directory, success_function, failure_function) {
    if (!file_directory || !success_function || !failure_function) {
        return false;
    }
     if (requester != null && requester.readyState != 0 && requester.readyState != 4) {
         requester.abort();
     } 
     
     try {
         requester = new XMLHttpRequest();
     }
     
     catch (error) {
         try {
             requester = new ActiveXObject("Microsoft.XMLHTTP");
         }
         catch (error) {
             return false;
         }
     }

     success = success_function;
     failure = failure_function;
     
     requester.open('GET', file_directory,true);   
     requester.onreadystatechange = stateHandler;
     requester.send(null);

 }

function stateHandler()
{
    if (requester.readyState == 4) {
        if (requester.status == 200) {
            eval(success);
        }
        else {
            eval(failure);
        }
    }
}
