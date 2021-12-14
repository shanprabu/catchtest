# Catch Code Test
Symfony console command to download a JSON line file from a public S3 Bucket, process the records and generate a CSV file.

## Setting up
Before setting up the repo please ensure your system has **PHP 7.3+**. To download and setup the repo

    $ git clone https://github.com/shanprabu/catchtest.git
    $ cd catchtest
    $ composer install

If not already in the .env file please add the following lines.
    DATA_URL=https://s3-ap-southeast-2.amazonaws.com/catch-code-challenge/challenge-1/orders.jsonl
    MAILER_DSN=smtp://3262a1cbce6ff6:935e2dc1572f11@smtp.mailtrap.io:2525?encryption=ssl&auth_mode=login
    GOOGLE_API_KEY=AIzaSyDcXBdS888RUVS3vVaU5RruB4xyM1bxTuE

For the purpose of testing I have used a MailTrap account for sending the email. The email doesn't actually get delivered to the receiver. Please replace the **MAILER_DSN** with your own SMTP credentials to test the email feature.

The Google API Key has been added for geocoding the customer address
## Running the script
After composer has successfully installed the required dependencies the script can be run by issuing the following command
    $ bin/console order:fetch

This command downloads the file **orders.jsonl** from the S3 bucket to the **storage** folder, parses the data and creates the file **out.csv** in the **storage** folder.
Optionally, you can provide an email address as a parameter to the script to get the CSV emailed.
    $ bin/console order:fetch your.name@email.com

## Running the unit test
The unit test can be run by issuing the following command
    $ bin/phpunit

### Contact Information
If you have any queries or encounter any issues while running the command please get in touch with
**Shanmuga Prabhu**
Email: <prabhu.shan@gmail.com>
Phone: 0468 347 151
