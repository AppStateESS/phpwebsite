function loadRequester() {
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
     requester.onreadystatechange = stateHandler;
     return true;
 }

function stateHandler()
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
