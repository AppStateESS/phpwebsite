<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class FC_Forms
{
    /**
     * Current working folder type
     * @var integer
     */
    private $ftype;

    /**
     * Id of current requested folder
     * @var integer
     */
    private $folder_id;
    private $title;
    private $content;
    private $factory;

    public function __construct()
    {
        $vars = filter_input_array(INPUT_GET, array('ftype' => FILTER_VALIDATE_INT,
            'folder_id' => FILTER_VALIDATE_INT));
        $this->ftype = $vars['ftype'];
        $this->folder_id = $vars['folder_id'];
        $this->loadFactory();
    }

    private function printFolderFiles()
    {
        $content = $this->factory->printFolderFiles();
        if (empty($content)) {
            echo 'No files found';
        } else {
            echo $content;
        }
    }

    /**
     * 
     * @return void
     */
    private function form()
    {
        if (empty($this->ftype)) {
            throw new \Exception('Missing folder type');
        }
        $this->content = $this->factory->getForm();
        $this->title = $this->factory->getTitle();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    private function loadFactory()
    {
        switch ($this->ftype) {
            case MULTIMEDIA_FOLDER:
                $this->factory = new \filecabinet\FC_Forms\FC_Multimedia($this->folder_id);
                break;

            case IMAGE_FOLDER:
                $this->factory = new \filecabinet\FC_Forms\FC_Images($this->folder_id);
                break;

            case DOCUMENT_FOLDER:
                $this->factory = new \filecabinet\FC_Forms\FC_Documents($this->folder_id);
                break;
        }
    }

    public function handle()
    {
        $request = \Server::getCurrentRequest();
        switch ($request->getVar('ckop')) {
            case 'form':
                $this->form();
                break;

            case 'save_file':
                $this->saveFile($request);
                exit();

            case 'delete_file':
                $this->deleteFile($request);
                exit();

            case 'list_folder_files':
                $this->printFolderFiles();
                exit();

            case 'get_file':
                $this->printFile($request);
                exit();
                
            case 'file_form':
                $this->fileForm($request);
                exit();

            default:
                throw new \Http\MethodNotAllowedException('Unknown request');
        }

        echo \Layout::wrap($this->getContent(), $this->getTitle(), true);
        exit();
    }
    
    private function fileForm(\Request $request)
    {
        $data['title'] = 'Form title';
        $data['content'] = '<p>Stuff</p>';
        echo json_encode($data);
    }

    private function printFile(\Request $request)
    {
        echo $this->factory->printFile($request->getVar('id'));
    }

    private function deleteFile(\Request $request)
    {
        if (!Current_User::authorized('filecabinet')) {
            $this->sendErrorHeader('No permissions to delete files');
        }

        $db = \Database::newDB();

        switch ($request->getVar('ftype')) {
            case DOCUMENT_FOLDER:
                $table = $db->addTable('documents');
                break;

            case IMAGE_FOLDER:
                $table = $db->addTable('images');
                break;

            case MULTIMEDIA_FOLDER:
                $table = $db->addTable('multimedia');
                break;
        }

        $table->addFieldConditional('id', $request->getVar('id'));
        $row = $db->selectOneRow();
        $filepath = $row['file_directory'] . $row['file_name'];
        if (is_file($filepath)) {
            unlink($filepath);
        }
        $db->delete();
    }

    public function saveFile(\Request $request)
    {
        if (Current_User::authorized('filecabinet')) {
            return;
        }

        $folder_id = $request->getVar('folder_id');
        $folder = new Folder($folder_id);
        switch ($folder->ftype) {
            case DOCUMENT_FOLDER:
                $this->uploadDocumentToFolder($folder, 'file');
                break;

            case IMAGE_FOLDER:
                $this->uploadImageToFolder($folder, 'file');
                break;

            case MEDIA_FOLDER:
                $this->uploadMediaToFolder($folder, 'file');
                break;
        }
    }

    private function uploadFileToFolder($folder, $filename, $ftype)
    {
        switch ($ftype) {
            case DOCUMENT_FOLDER:
                PHPWS_Core::initModClass('filecabinet', 'Document.php');
                $file_class = 'PHPWS_Document';
                break;

            case IMAGE_FOLDER:
                PHPWS_Core::initModClass('filecabinet', 'Image.php');
                $file_class = 'PHPWS_Image';
                break;

            case MEDIA_FOLDER:
                PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
                $file_class = 'PHPWS_Multimedia';
                break;
        }
        $upload = $_FILES[$filename];
        $destination_directory = $folder->getFullDirectory();

        if (!isset($_FILES[$filename])) {
            throw new \Exception('File upload could not be found');
        }

        $total_files = count($_FILES[$filename]['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $source_directory = $upload['tmp_name'][$i];
            $uploaded_file_name = $upload['name'][$i];
            $type = $upload['type'][$i];
            $error = $upload['error'][$i];
            $size = $upload['size'][$i];

            $file = new $file_class;
            $file->setFilename($uploaded_file_name);

            $new_file_name = $file->file_name;
            $destination_path = $destination_directory . $new_file_name;

            $this->checkDuplicate($destination_path);
            $this->checkMimeType($source_directory, $uploaded_file_name, $folder->ftype);
            $this->checkSize($source_directory, $size, $folder->ftype);

            move_uploaded_file($source_directory, $destination_path);
            //$file->setDirectory($folder->getFullDirectory());
            $file->setDirectory($destination_directory);
            $file->setSize($size);
            $file->file_type = $type;
            $file->setFolderId($folder->id);
            $title = preg_replace('/\.\w+$/', '', str_replace('_', ' ', $new_file_name));
            $file->setTitle(ucfirst($title));
            // save is false because the file is already written
            $this->saveUploadedFile($file);
        }
    }

    /**
     * Saves the $file object to the database depending on the file type. This is because Image, Document and Multimedia
     * have different save() parameters. 
     * @param mixed $file
     */
    private function saveUploadedFile($file)
    {
        $thumb = false;
        
        if (is_a($file, 'PHPWS_Image')) {
            $thumb = true;
            list($width, $height) = getimagesize($file->getPath());
            $file->width = (int)$width;
            $file->height = (int)$height;
            $result = $file->save(true, false, true);
        } elseif (is_a($file, 'PHPWS_Document')) {
            $result = $file->save(false);
        } elseif (is_a($file, 'PHPWS_Multimedia')) {
            $thumb = true;
            $result = $file->save(false, true);
        } else {
            throw new \Exception('Unknown upload file type');
        }
        if (PEAR::isError($result)) {
            $file->deleteFile();
            if ($thumb) {
                $file->deleteThumbnail();
            }
            $this->sendErrorHeader('An error occurred when trying to save this file');
        }
    }

    private function checkDuplicate($path)
    {
        if (is_file($path)) {
            $msg = "Duplicate file found";
            $this->sendErrorHeader($msg);
        }
    }

    private function sendErrorHeader($message)
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit($message);
    }

    private function checkMimeType($source_directory, $filename, $ftype)
    {
        switch ($ftype) {
            case DOCUMENT_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'document_files');
                break;
            case IMAGE_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'image_files');
                break;
            case MULTIMEDIA_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'media_files');
                break;
        }
        $ext = PHPWS_File::getFileExtension($filename);

        // First check if the extension is allowed for the current folder type.
        $type_array = explode(',', str_replace(' ', '', $type_list));
        if (!in_array($ext, $type_array)) {
            $this->sendErrorHeader('File type not allowed in folder');
        }

        // second check that file is the type it claims to be
        if (!PHPWS_File::checkMimeType($source_directory, $ext)) {
            $this->sendErrorHeader('Unknown file type');
        }
    }

    private function checkSize($source_file, $size, $ftype)
    {
        static $sizes;
        if (empty($sizes)) {
            $sizes = Cabinet::getMaxSizes();
        }

        switch ($ftype) {
            case DOCUMENT_FOLDER:
                $folder_max = $sizes['document'];
                break;

            case IMAGE_FOLDER:
                $folder_max = $sizes['image'];
                break;

            case MEDIA_FOLDER:
                $folder_max = $sizes['media'];
                break;
        }


        if ($size > $sizes['system'] || $size > $sizes ['form'] || $size > $sizes ['absolute'] || $size > $folder_max) {
            $this->sendErrorHeader('File size too large');
        }
    }

    private function uploadImageToFolder($folder, $filename)
    {
        $this->uploadFileToFolder($folder, $filename, IMAGE_FOLDER);
    }

    private function uploadDocumentToFolder(Folder $folder, $filename)
    {
        $this->uploadFileToFolder($folder, $filename, DOCUMENT_FOLDER);
    }

    private function uploadMediaToFolder($folder, $filename)
    {
        $this->uploadFileToFolder($folder, $filename, MULTIMEDIA_FOLDER);
    }

}
