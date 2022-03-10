import json
import xmltodict
import boto3
#
# parse the customData value from the CodeCommit trigger. Expects a comma-separated list of RSS feeds.
# Example trigger found at: https://us-east-1.console.aws.amazon.com/codesuite/codecommit/repositories/phibetakappastatic/triggers/phibetakappa-trigger/edit?region=us-east-1
# 
# @param event - dictionary generated from a CodeCommit Trigger attached to this Lambda function.
def get_feeds( event ):
	if 'Records' in event and 'customData' in event['Records'][0]:
		feeds = event['Records'][0]["customData"].split(",")
		for feed in feeds:
			yield feed.strip()
	yield None
#
# Receive a CodeCommit Trigger. Use the trigger's repository to determine which CodeCommit repository
# to update.  Call the get_feeds function that will return each comma separated RSS field supplied 
# as a "customData" value in the repository.  More information found on: https://docs.aws.amazon.com/codecommit/latest/userguide/how-to-notify-lambda-cc.html
#
# @param event - dictionary generated from a CodeCommit Trigger attached to this Lambda function.
# @return void
def code_commit_handler(event):
	client = boto3.client('codecommit')
	#
	# Get the repository from the event and show its git clone URL
	repository = event['Records'][0]['eventSourceARN'].split(':')[5]
	#response = codecommit.get_repository(repositoryName=repository)
	for file in get_feeds(event):	
		# aws codecommit batch-get-repositories --repository-names phibetakappastatic
		#
		# Get file from code commit
		
		get_response = client.get_file(
			repositoryName=repository,
			commitSpecifier='master',
			filePath=file
		)
		#
		# Parse the XML data and get JSON data
		data = xmltodict.parse(get_response["fileContent"].decode("utf-8"), xml_attribs=True)
		try:
			put_response = client.put_file(
				repositoryName=repository,
				branchName='master',
				fileContent=bytes(json.dumps(data), "utf-8"),
				filePath=file.replace(".xml",".json"),
				fileMode='NORMAL',
				parentCommitId=get_response["commitId"],
				commitMessage='XML to JSON',
				name='lambda function'
			)
			print("Clone URL: {}".format(put_response['repositoryMetadata']['cloneUrlHttp']))
		except Exception as e:
			print(e)
			print('Error getting repository {}. Make sure it exists and that your repository is in the same region as this function.'.format(repository))
			raise e

        
def lambda_handler(event, context):
	
	print("Context: {}".format(context))
	print("Event: {}".format(json.dumps(event, indent=4) ))
	ccr = None
	if 'Records' in event:
		ccr = code_commit_handler(event)
		return {
			'statusCode': 200,
			'body': json.dumps(data),
			'code-commit' : ccr
		}
    
if __name__ == "__main__":
	lambda_handler({},{})
