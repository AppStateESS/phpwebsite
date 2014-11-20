jQuery(function ($) {
    var fileDiv = document.getElementById("upload-area");
    var fileInput = document.getElementById("upload_filename");
    var files = {};
    fileInput.addEventListener("change", function (e) {
        console.log(this);
        var files = this.files
        showThumbnail(files)
    }, false)

    fileDiv.addEventListener("click", function (e) {
        $(fileInput).show().focus().click().hide();
        e.preventDefault();
    }, false)

    fileDiv.addEventListener("dragenter", function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);

    fileDiv.addEventListener("dragover", function (e) {
        e.stopPropagation();
        e.preventDefault();
    }, false);
    // for later
    /*
     fileDiv.addEventListener("drop", function (e) {
     e.stopPropagation();
     e.preventDefault();

     var dt = e.dataTransfer;
     var files = dt.files;
     handleFileUpload(files);
     showThumbnail(files)
     }, false);
     */
    function showThumbnail(files) {
        for (var i = 0; i < files.length; i++) {
            var file = files[i]
            var imageType = /image.*/
            if (!file.type.match(imageType)) {
                continue;
            }

            var image = document.createElement("img");
            //var thumbnail = document.getElementById("thumbnail");
            var thumbnail = $('#thumbnail');
            image.file = file;
            thumbnail.html(image);

            var reader = new FileReader()
            reader.onload = (function (aImg) {
                return function (e) {
                    aImg.src = e.target.result;
                };
            }(image))
            var ret = reader.readAsDataURL(file);
            var canvas = document.createElement("canvas");
            ctx = canvas.getContext("2d");
            image.onload = function () {
                ctx.drawImage(image, 100, 100)
            }
        }
    }
});