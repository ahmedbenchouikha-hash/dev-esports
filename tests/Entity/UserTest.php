<?php

namespace App\Tests\Entity;

use App\Entity\ChatbotConversation;
use App\Entity\User;
use App\Entity\UserProfile;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testIdIsNullByDefault(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $this->user->setEmail('test@esprit.tn');
        $this->assertSame('test@esprit.tn', $this->user->getEmail());
    }

    public function testSetAndGetUsername(): void
    {
        $this->user->setUsername('ahmed');
        $this->assertSame('ahmed', $this->user->getUsername());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $this->user->setEmail('user@example.com');
        $this->assertSame('user@example.com', $this->user->getUserIdentifier());
    }

    public function testGetUserIdentifierReturnsEmptyStringWhenEmailIsNull(): void
    {
        $this->assertSame('', $this->user->getUserIdentifier());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetRoles(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_USER', 'ROLE_ADMIN']);
        $roles = $this->user->getRoles();
        $this->assertCount(2, $roles);
    }

    public function testSetAndGetPassword(): void
    {
        $this->user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $this->user->getPassword());
    }

    public function testSetAndGetTypeuser(): void
    {
        $this->user->setTypeuser('manager');
        $this->assertSame('manager', $this->user->getTypeuser());
    }

    public function testDefaultApprovalStatusIsPending(): void
    {
        $this->assertSame('pending', $this->user->getApprovalStatus());
    }

    public function testSetApprovalStatus(): void
    {
        $this->user->setApprovalStatus('approved');
        $this->assertSame('approved', $this->user->getApprovalStatus());
    }

    public function testIsApproved(): void
    {
        $this->assertFalse($this->user->isApproved());
        $this->user->setApprovalStatus('approved');
        $this->assertTrue($this->user->isApproved());
    }

    public function testIsPending(): void
    {
        $this->assertTrue($this->user->isPending());
        $this->user->setApprovalStatus('approved');
        $this->assertFalse($this->user->isPending());
    }

    public function testIsRejected(): void
    {
        $this->assertFalse($this->user->isRejected());
        $this->user->setApprovalStatus('rejected');
        $this->assertTrue($this->user->isRejected());
    }

    public function testSetAndGetConfirmationFile(): void
    {
        $this->user->setConfirmationFile('document.pdf');
        $this->assertSame('document.pdf', $this->user->getConfirmationFile());
    }

    public function testConfirmationFileIsNullByDefault(): void
    {
        $this->assertNull($this->user->getConfirmationFile());
    }

    public function testSetAndGetFirstName(): void
    {
        $this->user->setFirstName('Ahmed');
        $this->assertSame('Ahmed', $this->user->getFirstName());
    }

    public function testSetAndGetLastName(): void
    {
        $this->user->setLastName('Ben Chouikha');
        $this->assertSame('Ben Chouikha', $this->user->getLastName());
    }

    public function testSetAndGetBirthDate(): void
    {
        $date = new \DateTime('2000-01-15');
        $this->user->setBirthDate($date);
        $this->assertSame($date, $this->user->getBirthDate());
    }

    public function testSetAndGetProfile(): void
    {
        $profile = $this->createMock(UserProfile::class);
        $profile->method('getUser')->willReturn(null);
        $profile->expects($this->once())->method('setUser')->with($this->user);

        $this->user->setProfile($profile);
        $this->assertSame($profile, $this->user->getProfile());
    }

    public function testSetProfileToNull(): void
    {
        $this->user->setProfile(null);
        $this->assertNull($this->user->getProfile());
    }

    public function testSetProfileDoesNotResetIfAlreadyOwned(): void
    {
        $profile = $this->createMock(UserProfile::class);
        $profile->method('getUser')->willReturn($this->user);
        $profile->expects($this->never())->method('setUser');

        $this->user->setProfile($profile);
        $this->assertSame($profile, $this->user->getProfile());
    }

    public function testChatbotConversationsEmptyByDefault(): void
    {
        $this->assertCount(0, $this->user->getChatbotConversations());
    }

    public function testAddChatbotConversation(): void
    {
        $conv = $this->createMock(ChatbotConversation::class);
        $conv->expects($this->once())->method('setUser')->with($this->user);

        $this->user->addChatbotConversation($conv);
        $this->assertCount(1, $this->user->getChatbotConversations());
        $this->assertTrue($this->user->getChatbotConversations()->contains($conv));
    }

    public function testAddSameConversationTwiceDoesNotDuplicate(): void
    {
        $conv = $this->createMock(ChatbotConversation::class);
        $conv->expects($this->once())->method('setUser');

        $this->user->addChatbotConversation($conv);
        $this->user->addChatbotConversation($conv);
        $this->assertCount(1, $this->user->getChatbotConversations());
    }

    public function testRemoveChatbotConversation(): void
    {
        $conv = $this->createMock(ChatbotConversation::class);
        $conv->method('getUser')->willReturn($this->user);
        $conv->expects($this->exactly(2))
            ->method('setUser')
            ->withConsecutive([$this->user], [null]);

        $this->user->addChatbotConversation($conv);
        $this->user->removeChatbotConversation($conv);
        $this->assertCount(0, $this->user->getChatbotConversations());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $this->user->eraseCredentials();
        $this->assertTrue(true);
    }

    public function testFluentInterface(): void
    {
        $result = $this->user
            ->setEmail('test@esprit.tn')
            ->setUsername('ahmed')
            ->setPassword('secret')
            ->setTypeuser('player')
            ->setApprovalStatus('approved')
            ->setFirstName('Ahmed')
            ->setLastName('Ben Chouikha')
            ->setBirthDate(new \DateTime('2000-01-01'))
            ->setConfirmationFile('file.pdf');

        $this->assertSame($this->user, $result);
    }
}
