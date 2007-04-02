function loadRequester(file_directory, success, failure) {
    if (!file_directory || !success || !failure) {
        return false;
    }
     if (requester != null && requester.readyState != 0 && requester.readyState != 4)
         {
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
     
     requester.open('GET', file_directory);   
     requester.send(null);

     success_function = new Function(success);
     failure_function = new Function(failure);

     requester.onreadystatechange = stateHandler;
     return true;
 }

function stateHandler(success, failure)
{
    if (requester.readyState == 4) {
        if (requester.status == 200) {
            success_function();
        }
        else {
            failure_function();
        }
    }

    return true;
}
