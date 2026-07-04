<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller used to test that a regular Symfony form (not built with
 * EasyAdmin fields) can be rendered with EasyAdmin's form theme, both
 * inside and outside an admin context.
 */
class CustomFormPageController extends AbstractController
{
    #[AdminRoute(path: '/custom-page-with-form', name: 'custom_form_page')]
    public function customFormPage(): Response
    {
        return $this->render('admin/custom_form_page.html.twig', [
            'form' => $this->createCustomForm(),
        ]);
    }

    #[Route('/custom-form-without-admin-context', name: 'custom_form_no_context')]
    public function customFormWithoutAdminContext(): Response
    {
        return $this->render('admin/custom_form_no_context.html.twig', [
            'form' => $this->createCustomForm(),
        ]);
    }

    private function createCustomForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('name', TextType::class, ['help' => 'Your full name'])
            ->add('bio', TextareaType::class, ['required' => false])
            ->add('color', ChoiceType::class, ['choices' => ['Red' => 'red', 'Blue' => 'blue']])
            ->add('subscribe', CheckboxType::class, ['required' => false])
            ->getForm();
    }
}
