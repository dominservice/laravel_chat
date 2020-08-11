<?php


use Dominservice\LaravelChat\Entities\Message;
use Dominservice\LaravelChat\LaravelChat;

class EndToEndTest extends TestCaseDb  {

	/**
	 * @test
	 */
	public function mark_message_as_read_and_unread() {
		$user1 = 4;
		$user2 = 9;
		$content = 'default content';

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $content);

		$conv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);
		$message = $conv->getLastMessage();
		$this->assertEquals(LaravelChat::UNREAD, $message->getStatus());

		$this->laravel_chat->markMessageAsRead($message->getId(), $user2);

		$conv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);
		$message = $conv->getLastMessage();
		$this->assertEquals(LaravelChat::READ, $message->getStatus());

		$this->laravel_chat->markMessageAsUnread($message->getId(), $user2);

		$conv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);
		$message = $conv->getLastMessage();
		$this->assertEquals(LaravelChat::UNREAD, $message->getStatus());
	}

	/**
	 * @test
	 */
	public function mark_unread_messages_as_read() {
		$user1 = 4;
		$user2 = 9;
		$content = 'default content';

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $content);
		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $content);
		$this->assertEquals(2, $this->laravel_chat->getNumOfUnreadMsgs($user2));

		$this->laravel_chat->markReadAllMessagesInConversation($createdConvId, $user2);
		$this->assertEquals(0, $this->laravel_chat->getNumOfUnreadMsgs($user2));
	}

	/**
	 * @test
	 */
	public function get_number_of_unread_messages() {
		$user1 = 4;
		$user2 = 9;
		$content = 'default content';

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->assertEquals(0, $this->laravel_chat->getNumOfUnreadMsgs($user2));

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $content);
		$this->assertEquals(1, $this->laravel_chat->getNumOfUnreadMsgs($user2));

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $content);
		$this->assertEquals(2, $this->laravel_chat->getNumOfUnreadMsgs($user2));
	}

	/**
	 * @test
	 */
	public function send_message_between_two_users() {
		$user1 = 4;
		$user2 = 9;
		$content = 'default content';
		$dispatcher = \Mockery::mock('Illuminate\Contracts\Events\Dispatcher');

		$arr = [
			'senderId' => $user1,
			'convUsersIds' => [$user1, $user2],
			'content' => $content,
			'convId' => 1,
		];

		$dispatcher->shouldReceive('fire')->with('message.sent', [$arr]);

		$this->laravel_chat = new \Dominservice\LaravelChat\LaravelChat(
			new \Dominservice\LaravelChat\Repositories\EloquentLaravelChatRepository('', 'users', 'id', $this->db),
			$dispatcher);

		$this->laravel_chat->sendMessageBetweenTwoUsers($user1, $user2, $content);

		$data = $this->laravel_chat->getConversationByTwoUsers($user1, $user2);
		$this->assertEquals(1, $data);
	}

	/**
	 * @test
	 */
	public function check_conversation_num_of_participants() {
		$user1 = 4;
		$user2 = 9;
		$user3 = 11;
		$user4 = 15;
		$user5 = 17;
		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$fullConv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);
		$this->assertEquals(2, $fullConv->getNumOfParticipants());

		//check 5 users
		$data = $this->laravel_chat->createConversation([$user1, $user2, $user3, $user4, $user5]);
		$createdConvId = $data['convId'];

		$fullConv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);
		$this->assertEquals(5, $fullConv->getNumOfParticipants());
	}

	/**
	 * @test
	 */
	public function check_user_is_in_conversation() {
		$user1 = 4;
		$user2 = 9;

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];



		$this->assertTrue(
			$this->laravel_chat->isUserInConversation($createdConvId, $user1)
		);

		$this->assertTrue(
			$this->laravel_chat->isUserInConversation($createdConvId, $user2)
		);
	}

	/**
	 * @test
	 */
	public function check_user_is_not_in_conversation() {
		$user1 = 4;
		$user2 = 9;
		$user3 = 11;

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];



		$this->assertFalse(
			$this->laravel_chat->isUserInConversation($createdConvId, $user3)
		);


	}

	/**
	 * @test
	 */
	public function get_conversation_users() {
		$user1 = 4;
		$user2 = 9;
		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, 'message content');

		$fullConv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);

		$participants = $fullConv->getAllParticipants();
		$participants = array_flip($participants);
		$this->assertEquals(2, sizeof($participants));
		$this->assertTrue(isset($participants[$user1]));
		$this->assertTrue(isset($participants[$user2]));
	}

	/**
	 * @test
	 */
	public function check_conversation_type_is_couple() {
		$user1 = 4;
		$user2 = 9;
		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, 'message content');

		$fullConv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);

		$this->assertEquals('couple', $fullConv->getType());
	}

	/**
	 * @test
	 */
	public function add_message_to_conversation() {
		$user1 = 1;
		$user2 = 2;
		$msgContent = 'not relevant content';

		$dispatcher = \Mockery::mock('Illuminate\Contracts\Events\Dispatcher');

		$arr = [
			'usersIds' => [$user1, $user2],
			'convId' => 1,
		];
		$dispatcher->shouldReceive('fire')->with('conversation.created', [$arr]);

		$arr = [
			'senderId' => $user1,
			'convUsersIds' => [$user1, $user2],
			'content' => $msgContent,
			'convId' => 1,
		];
		$dispatcher->shouldReceive('fire')->with('message.sent', [$arr]);



		$this->laravel_chat = new \Dominservice\LaravelChat\LaravelChat(
			new \Dominservice\LaravelChat\Repositories\EloquentLaravelChatRepository('', 'users', 'id', $this->db),
			$dispatcher);

		$data = $this->laravel_chat->createConversation([$user1, $user2]);
		$createdConvId = $data['convId'];

		$this->laravel_chat->addMessageToConversation($createdConvId, $user1, $msgContent);

		$conv = $this->laravel_chat->getConversationMessages($createdConvId, $user2);

		$this->assertEquals(1, $conv->getNumOfMessages());

		/** @var Message $msg */
		$msg = $conv->getLastMessage();

		$this->assertEquals($msgContent, $msg->getContent());
		$this->assertEquals($user1, $msg->getSender());
		$this->assertEquals(0, $msg->getSelf());
		$this->assertEquals(LaravelChat::UNREAD, $msg->getStatus());
	}

	/**
	 * @test
	 */
	public function get_conversation_by_2_users() {

		$data = $this->laravel_chat->createConversation([1,5]);
		$createdConvId = $data['convId'];

		$foundConvId = $this->laravel_chat->getConversationByTwoUsers(1,5);

		$this->assertEquals($createdConvId, $foundConvId);
	}

	/**
	 * @test
	 * @expectedException     Dominservice\LaravelChat\Exceptions\NotEnoughUsersInConvException
	 */
	public function create_conversation_with_not_enough_users() {
		$data = $this->laravel_chat->createConversation([1]);
	}

	public function todo() {
		//TODO add tests for those methods:
		//getUserConversations
		//markUnreadAllMessagesInConversation
		//deleteConversation
		//getUsersInConversation
	}
}