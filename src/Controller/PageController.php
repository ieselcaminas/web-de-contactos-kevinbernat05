<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Contacto;
use App\Entity\Provincia;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ContactoRepository;
use App\Form\ContactoFormType;
use Symfony\Component\HttpFoundation\Request;


final class PageController extends AbstractController

{

    private $contactos = [

        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],

        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],

        5 => ["nombre" => "Sergio Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],

        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],

        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]

    ];



    #[Route('contacto/insertar', name: 'insertar_contacto')]
    public function insertar(ManagerRegistry $doctrine)
    {
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
    }
        $entityManager = $doctrine->getManager();
        foreach($this->contactos as $c){
            $contacto = new Contacto();
            $contacto->setNombre($c['nombre']);
            $contacto->setTelefono($c['telefono']);
            $contacto->setEmail($c['email']);
            $entityManager->persist($contacto);
        }
        try {
            $entityManager->flush();
            return new Response('Contactos insertados correctamente');
        } catch (\Exception $e) {
            return new Response('Error al insertar los contactos: ' . $e->getMessage());
        }
    }
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('login/inicio.html.twig');
    }

    #[Route('/', name: 'inicio')]
    public function inicio(ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
    }
    // Obtenemos todos los contactos de la base de datos
    $contactos = $doctrine->getRepository(Contacto::class)->findAll();

    // Renderizamos la plantilla de lista de contactos
    return $this->render('lista.html.twig', [
        'contactos' => $contactos
    ]);
    }


    #[Route('/contacto/{id}', name: 'ficha_contacto', requirements: ['id' => '\d+'])]

    public function ficha(ManagerRegistry $doctrine, $id): Response{
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
        }
        $repositorio = $doctrine->getRepository(Contacto::class);

        //Si no existe el elemento con dicha clave devolvemos null
        $contacto = $repositorio->find($id);

        return $this->render('ficha_contacto.html.twig', [
        'contacto' => $contacto
        ]);
    }


    #[Route('/contacto/buscar/{texto}', name: 'buscar_contacto')]
    public function buscar(ContactoRepository $repositorio, $texto): Response{
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
    }
        //Si no existe el elemento con dicha clave devolvemos null
        $contacto = $repositorio->findByName($texto);

        return $this->render('lista_contacto.html.twig', [
        'contactos' => $contacto
        ]);
    }

    #[Route('/contacto/update/{telefono}/{nombre}', name: 'modificar_contacto')]
    public function update(ManagerRegistry $doctrine,$telefono, $nombre): Response{
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
        }
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($nombre);
        if ($contacto){
            $contacto->setTelefono($telefono);
            try {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig', [
                    'contacto' => $contacto
                ]);
            } catch (\Exception $e) {
                return new Response('Error al modificar el contacto: ' . $e->getMessage());
            }   
        } else 
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
    }

    #[Route('/contacto/delete/{id}', name: 'eliminar_contacto')]
    public function delete(ManagerRegistry $doctrine, $id): Response {
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
    }
    $entityManager = $doctrine->getManager();
    $repositorio = $doctrine->getRepository(Contacto::class);
    $contacto = $repositorio->find($id); // Busca por ID
    if ($contacto) {
        $entityManager->remove($contacto);
        $entityManager->flush();
        return $this->redirectToRoute('inicio');
    }
    return $this->render('ficha_contacto.html.twig', [
        'contacto' => null
    ]);
}   

    #[Route('/contacto/insertarConProvincia', name: 'insertar_con_provincia')]
    public function insertarConProvincia(ManagerRegistry $doctrine): Response{
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
        }
        $entityManager = $doctrine->getManager();
        $provincia = new Provincia();

        $provincia->setNombre("Alicante");
        $contacto = new Contacto();

        $contacto->setNombre("Flavius");
        $contacto->setTelefono("123456789");
        $contacto->setEmail("amiwis@contacto.es");
        $contacto->setProvincia($provincia);

        $entityManager->persist($provincia);
        $entityManager->persist($contacto);

        $entityManager->flush();
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
            ]);
    }

    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request) {
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
        }
        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData();
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('ficha_contacto', ["id" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }

    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements:["codigo"=>"\d+"])]
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo) {
        if (!$this->getUser()) {
        return $this->redirectToRoute('index'); // página de login/inicio
    }
    $repositorio = $doctrine->getRepository(Contacto::class);
    //En este caso, los datos los obtenemos del repositorio de contactos
    $contacto = $repositorio->find($codigo);
    if ($contacto){
        $formulario = $this->createForm(ContactoFormType::class, $contacto);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            //Esta parte es igual que en la ruta para insertar
            $contacto = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('ficha_contacto', ["id" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
        }else{
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => NULL
        ]);
        }
    }

}
     
