# Python Lambda RSS function 
This function will take files from your CodeCommit repo, convert them to JSON and then put them back in there. 
If you haven't got Python3.9 installed, install it first, then do the following steps.

1. Create a new Python Lambda function version 3.9
2. Select _Author from scratch_
3. Paste `lambda_function.py` contents into the boilerplate created by Amazon.
4. After creating your function and pasting the code, go to Function overview and click "Layers"
5. Upload the package.zip as the layer you are adding.  This contains the requests library and the xmltodict library
6. Create a Test case in the code editor.
7. Deploy the Lambda function
8. Under Funciton Overview select "Add Trigger"
9. Create a CodeCommit trigger with read access to your CodeCommit repository
10. In your Codecommit repository, go to Source > Repository > Settings. Select the Triggers tab
   https://docs.aws.amazon.com/codecommit/latest/userguide/how-to-notify-lambda.html
   * Create a AWS Lambda trigger
   * Select "All Repository Events" under Events
   * Select the Python Lambda function you created in an earlier step
   * Under "Custom data - optional", enter a comma-separated list of the paths to your RSS feeds relative to the CodeCommit repository's root, for example: /_resources/rss/news.xml, /_resources/rss/directory.xml
