## Project setup

- [Clone the github repository](https://github.com/georgetulev/demo.git).
- Create `.env` file and copy the content of `.env.example` file in it.
- Execute `./vendor/bin/sail up` to start the container. In case port 8888 is already in use change the `APP_PORT` variable in your `.env` file.
- Go to `http://localhost:8888/conversations/1` in your browser to see the result of the initial [customer](https://github.com/jiminny/join-the-team/blob/master/assets/customer-channel.txt)/[user](https://github.com/jiminny/join-the-team/blob/master/assets/user-channel.txt) channel files provided for the task.
- In order to test the functionality with different data create a new folder `/storage/app/conversations/2`. Add `user-channel.txt` and `customer-channel.txt` to the folder and hit `http://localhost:8888/conversations/2` to get the result.  
- In addition, I've added an artisan command that could be used from the terminal like so `./vendor/bin/sail artisan process:conversation 1`.
