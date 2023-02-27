<?php

namespace App\Entity;

use App\Entity\Trait\ArchivableEntity;
use App\Entity\Trait\BlameableEntity;
use App\Entity\Trait\TimestampableEntity;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\EquatableInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: ["email", "pseudo"], message: User::VALUE_ALREADY_USED)]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    use TimestampableEntity, BlameableEntity, ArchivableEntity;

    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_USER = "ROLE_USER";
    const ROLE_LABEL =[
        self::ROLE_ADMIN => "Administrateur",
        self::ROLE_USER => "Utilisateur",
    ];
    const EMAIL_PATTERN = "/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/";
    const EMAIL_PATTERN_MESSAGE = "L'adresse email n'est pas valide";
    const PASSWORD_PATTERN = "/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[\p{P}\p{S}]).{10,}$/";
    const PASSWORD_PATTERN_HELP = "Le mot de passe doit contenir au moins 10 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial";
    const PASSWORD_PATTERN_MESSAGE = "Mot de passe non conforme";
    const VALUE_ALREADY_USED = "Cette valeur est déjà en cours d'utilisation par un autre utilisateur.";
    const EMAIL_ALREADY_USED = "Cet email est déjà en cours d'utilisation par un autre utilisateur.";
    const PSEUDO_ALREADY_USED = "Ce pseudo est déjà en cours d'utilisation par un autre utilisateur.";
    const AVATAR_SIZE = 1048576;
    const AVATAR_TYPE = ["image/png", "image/jpg", "image/jpeg", "image/webp"];
    const AVATAR_EXTENSION = ["png", "jpg", "jpeg", "webp"];
    const AVATAR_TYPE_INVALID = "Le fichier doit être de type jpeg, jpg png ou webp";
    const AVATAR_SIZE_INVALID = "Le fichier doit faire moins de 1Mo";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: "128", unique: "true")]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: "64")]
    private string $firstname = "";

    #[ORM\Column(type: Types::STRING, length: "64")]
    private string $lastname = "";

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private bool $emailVerified = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private bool $passwordChanged = false;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $avatar;

    #[Assert\File(maxSize: self::AVATAR_SIZE)]
    #[Assert\Image(mimeTypes: self::AVATAR_TYPE)]
    #[Vich\UploadableField(mapping: "user_avatar", fileNameProperty: "avatar")]
    private ?File $avatarFile = null;

    // will not be stored in db
    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::STRING, length: "64", unique: "true")]
    private string $pseudo;

    #[ORM\Column(type: "datetime")]
    private \DateTime $agreedTermsAt;

    public function __construct()
    {
    }

    public function isEqualTo(UserInterface $user): bool 
    {
        $result = true;
        if (!$user instanceof User) {
            $result = false;
        }

        if (!empty($user->archivedAt)) {
            $result = false;
        }

        return $result;
    }

        
    final public function getId(): ?int
    {
        return $this->id;
    }

    final public function getEmail(): string
    {
        return $this->email;
    }

    final public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    final public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    final public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    final public function getRoles(): array
    {
        return $this->roles;
    }

    final public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    final public function addRole(string $role): self
    {
        if (! in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    final public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    final public function getPassword(): ?string
    {
        return $this->password;
    }

    final public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    final public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    final public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    final public function getFirstname(): string
    {
        return $this->firstname;
    }

    final public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    final public function getLastname(): string
    {
        return $this->lastname;
    }

    final public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    final public function getFullname(): string
    {
        return $this->getFirstname() . " " . $this->getLastname();
    }

    final public function getSlugname(): string
    {
        return str_replace([" ", "-"], "", strtolower($this->getFullname()));
    }

    final public function getInitials(): string
    {
        return mb_strtoupper(
            mb_substr($this->getFirstname(), 0, 1)
            . mb_substr($this->getLastname(), 0, 1)
        );
    }

    final public function getMainRole(): string
    {
        return $this->getRoles()[0];
    }

    final public function getMainRoleLabel(): string
    {
        return self::ROLE_LABEL[$this->getRoles()[0]];
    }

    final public function hasEmailVerified(): ?bool
    {
        return $this->emailVerified;
    }

    final public function setEmailVerified(bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    final public function hasPasswordChanged(): bool
    {
        return $this->passwordChanged;
    }

    final public function setPasswordChanged(bool $passwordChanged): self
    {
        $this->passwordChanged = $passwordChanged;

        return $this;
    }

    final public function getPasswordChanged(): ?bool
    {
        return $this->passwordChanged;
    }

    final public function __toString(): string
    {
        return $this->getFullname();
    }

    final public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    final public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    final public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    final public function setAvatarFile(?File $avatarFile): self
    {
        $this->avatarFile = $avatarFile;
        if ($this->avatarFile instanceof UploadedFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    final public function __serialize(): array
    {
        return [
            "id" => $this->id,
            "email" => $this->email,
            "password" => $this->password,
        ];
    }

    final public function __unserialize(array $data): void
    {
        $this->id = $data["id"] ?? null;
        $this->email = $data["email"] ?? null;
        $this->password = $data["password"] ?? null;
    }

    final public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    final public function setPlainPassword(?string $plainPassword = null): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    final public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    final public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }


    final public function setAgreedTermsAt(\DateTime $agreedTermsAt): self
    {
        $this->agreedTermsAt = $agreedTermsAt;

        return $this;
    }

    final public function getAgreedTermsAt(): \DateTime
    {
        return $this->agreedTermsAt;
    }

    final public function agreeToTerms()
    {
        $this->agreedTermsAt = new \DateTime();
    }
}
