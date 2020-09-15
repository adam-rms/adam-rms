import json
import os, time
import boto3
import pymysql
# to edit put it in a directory with all the requirements and then run following to upload to AWS. The regex makes sure it includes hidden folders: zip -r package.zip * .[^.]* 
os.environ['TZ'] = 'UTC'
time.tzset()

def deletor():
    print("[INFO] Starting")
    
    '''
        Establish a connection to the Database
    '''
    
    dbConnection = pymysql.connect(
        host=os.environ.get('dbHost'),
        user=os.environ.get('dbUser'),
        passwd=os.environ.get('dbPass'),
        database=os.environ.get('dbDatabase')
    )
    
    dbCursor = dbConnection.cursor(pymysql.cursors.DictCursor)
    print("[INFO] DB Connected")
    
    '''
        Establish a S3 connection
    '''
    s3client = boto3.client('s3')

    print("[INFO] S3 Connected")
    
    print("[INFO] Remove files from S3 that are due to be deleted")
    dbCursor.execute("SELECT s3files_bucket,s3files_path,s3files_filename,s3files_extension,s3files_id FROM s3files WHERE s3files_meta_deleteOn IS NOT NULL AND s3files_meta_deleteOn <= CURRENT_TIMESTAMP() AND s3files_meta_physicallyStored = 1") #Select everything that needs deleting
    listOfFiles = dbCursor.fetchall()
    counter = 0
    for file in listOfFiles:
        deleteRequest = s3client.delete_object(Bucket=str(file['s3files_bucket']), Key=str(file['s3files_path'])+"/"+str(file['s3files_filename'])+"."+str(file['s3files_extension']))
        if (True):
            #Not yet possible to verify if the file has been deleted or not
            print("[RESULT] Found file that needs deleting (id " + str(file['s3files_id']) + " = " + str(file['s3files_path'])+"/"+str(file['s3files_filename'])+"."+str(file['s3files_extension']) + ") & has now been deleted - updating DB")
            dbCursor.execute("UPDATE s3files SET s3files_meta_physicallyStored = 0 WHERE s3files_id = '" + str(file['s3files_id']) + "'")
            dbConnection.commit()
            counter = counter + 1
        else:
            print("[ERROR] Could not delete file with id " + str(file['s3files_id']) + " and path " + str(file['s3files_path'])+"/"+str(file['s3files_filename'])+"."+str(file['s3files_extension']))
    
    return counter
def lambda_handler(event, context):
    # TODO implement
    count = deletor()
    return {
        'statusCode': 200,
        'body': json.dumps('Deleted ' + str(count) + ' file()')
    }
