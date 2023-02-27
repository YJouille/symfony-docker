<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    )
    {
    }

    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("firstname", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                ],
                "empty_data" => "",
                "label" => "Prénom",
                "attr" => [
                    "autocomplete" => "off",
                    "class" => "form-control",
                    "placeholder" => "Prénom"
                ]
            ])
            ->add("lastname", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                ],
                "empty_data" => "",
                "label" => "Nom",
                "attr" => [
                    "autocomplete" => "off",
                    "class" => "form-control",
                    "placeholder" => "Nom"
                ]
            ])
            ->add("pseudo", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                ],
                "empty_data" => "",
                "label" => "Pseudo",
                "attr" => [
                    "autocomplete" => "off",
                    "class" => "form-control",
                    "placeholder" => "Pseudo"
                ]
            ])
            ->add("email", EmailType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                    new Regex([
                        "pattern" => User::EMAIL_PATTERN,
                        "message" => User::EMAIL_PATTERN_MESSAGE
                    ])
                ],
                "empty_data" => "",
                "label" => "Email",
                "attr" => [
                    "autocomplete" => "off",
                    "class" => "form-control",
                    "placeholder" => "Email"
                ],
            ])
            ->add("submit", SubmitType::class, [
                "label" => "Valider",
                "attr" => [
                    "class" => "btn-primary",
                ]
            ]);
        if ($this->security->isGranted(User::ROLE_ADMIN)) {
            $builder->add("roles", ChoiceType::class, [
                "placeholder" => "Choisissez dans cette liste",
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                ],
                "empty_data" => "",
                "label" => "Rôles",
                "multiple" => true,
                "choices" => $this->getChoicesRoles(),
                "attr" => [
                    "autocomplete" => "off",
                    "class" => "form-control select2-custom"
                ]
            ]);
        };
        if ($this->requestStack->getCurrentRequest()->get('_route') == "registration") {
            $builder->add("agreeToTerms", CheckboxType::class, [
                "mapped" => false,
                "label" => "J'accepte les conditions d'utilisations",
                "constraints" => [
                    new IsTrue([
                        "message" => "Vous devez accepter les conditions d'utilisations"
                    ])
                ]
            ]);
        };
    }

    final public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => User::class,
        ]);
    }
    

    private function getChoicesRoles()
    {
        $choices = User::ROLE_LABEL;
        $output = [];
        foreach ($choices as $key => $value) {
            $output[$value] = $key;
        }

        ksort($output);

        return $output;
    }

}
