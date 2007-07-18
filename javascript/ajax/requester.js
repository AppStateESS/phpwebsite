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
     requester.onreadystatechange = stateHandler(success, failure);
 }

function stateHandler(success, failure)
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
