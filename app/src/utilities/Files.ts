import { faFileImage, faFilePdf, faFileWord, faFilePowerpoint, faFileExcel, faFileCsv, faFileAudio, faFileVideo, faFileArchive, faFileCode, faFileAlt, faFile} from '@fortawesome/free-solid-svg-icons'
import Api from "./Api";

/**
*  Get the URL for the resource in AdamRMS s3 Bucket
 * @param fileid s3files_id
 * @param size s3files_meta_size 
 * @returns string 
 */
export async function s3url (fileid: string, size: number) {
    const response = await Api("file/", {"f": fileid, "d": "force", "s": size});
    return(response.url);
}

/**
 * Convert a file extension to a Font Awesome Icon
 * @param extension File extension
 * @returns Font Awesome Icon string
 */
export function fileExtensionToIcon (extension: string) {
    switch (extension.toLowerCase()) {
      case "gif":
      case "jpeg":
      case "jpg":
      case "png":
        return faFileImage;
      case "pdf":
        return faFilePdf;
      case "doc":
      case "docx":
        return faFileWord;
      case "ppt":
      case "pptx":
        return faFilePowerpoint;
      case "xls":
      case "xlsx":
        return faFileExcel;
      case "csv":
        return faFileCsv;
      case "aac":
      case "mp3":
      case "ogg":
        return faFileAudio;
      case "avi":
      case "flv":
      case "mkv":
      case "mp4":
        return faFileVideo;
      case "gz":
      case "zip":
        return faFileArchive;
      case "css":
      case "html":
      case "js":
        return faFileCode;
      case "txt":
        return faFileAlt;
      default:
        return faFile;
    }
}

/**
 * Convert file size number to string representation
 * @param size number file size
 * @returns string file size + byte qualifier
 */
export function formatSize(size: number) {
    let sizeString = "";
    if (size >= 1073741824) {
        sizeString = (size / 1073741824).toFixed(1) + ' GB';
    } else if (size >= 100000) {
        sizeString = (size / 1048576).toFixed(1) + ' MB';
    } else if (size >= 1024) {
        sizeString = (size / 1024).toFixed(0) + ' KB';
    } else if (size > 1) {
        sizeString = size + ' bytes';
    } else if (size == 1) {
        sizeString = size + ' byte';
    } else {
        sizeString = '0 bytes';
    }
    return sizeString;
}