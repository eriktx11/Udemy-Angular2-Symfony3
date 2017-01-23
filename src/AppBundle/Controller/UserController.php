<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \BackendBundle\Entity\User;

class UserController extends Controller {

    public function newAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $json = $request->get("json", null);
        $parasm = json_decode($json);
        $data = array("status" => "error", "code" => 400, "msg" => "Error, user not created");
        if ($json != null) {
            $createdAt = new \DateTime("now");
            $imaga = null;
            $role = "user";
            $email = (isset($parasm->email)) ? $parasm->email : null;
            $name = (isset($parasm->name)) && ctype_alpha($parasm->name) ? $parasm->name : null;
            $surname = (isset($parasm->surname)) && ctype_alpha($parasm->surname) ? $parasm->surname : null;
            $password = (isset($parasm->password)) ? $parasm->password : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Invalid email format !!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            if ($email != null && count($validate_email) == 0 && $password != null && $name != null && $surname != null) {
                $user = new User();
                $user->setCreatedAt($createdAt);
                $user->setImage($imaga);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository("BackendBundle:User")->findBy(array
                    (
                    "email" => $email
                        )
                ); //query db to see if user exists
                // if user does not exists then create new record
                if (count($isset_user) == 0) {
                    $em->persist($user);
                    $em->flush();

                    $data["status"] = 'Sucess';
                    $data["code"] = 200;
                    $data["msg"] = 'New user created';
                } else {
                    $data = array("status" => "error", "code" => 400, "msg" => "Error, user exists already");
                }
            }
        }
        return $helpers->json($data);
    }

    public function editAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);
        
        
        if ($authCheck == true) {
            $identity = $helpers->authCheck($hash,true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(array (
                "id" => $identity->sub
            ));
            
            $json = $request->get("json", null);
            $parasm = json_decode($json);
            $data = array("status" => "error", "code" => 400, "msg" => "Error, user not updated");
            if ($json != null) {
                $createdAt = new \DateTime("now");
                $imaga = null;
                $role = "user";
                $email = (isset($parasm->email)) ? $parasm->email : null;
                $name = (isset($parasm->name)) && ctype_alpha($parasm->name) ? $parasm->name : null;
                $surname = (isset($parasm->surname)) && ctype_alpha($parasm->surname) ? $parasm->surname : null;
                $password = (isset($parasm->password)) ? $parasm->password : null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "Invalid email format !!";
                $validate_email = $this->get("validator")->validate($email, $emailConstraint);
                if ($email != null && count($validate_email) == 0 && $name != null && $surname != null) {
                    
                    $user->setCreatedAt($createdAt);
                    $user->setImage($imaga);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    if($password!=null){
                      $pwd = hash('sha256', $password);
                      $user->setPassword($pwd);
                    }

                    $em = $this->getDoctrine()->getManager();
                    $isset_user = $em->getRepository("BackendBundle:User")->findBy(array
                        (
                        "email" => $email
                            )
                    ); //query db to see if user exists
                    // if user does not exists then create new record
                    if (count($isset_user) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();

                        $data["status"] = 'Sucess';
                        $data["code"] = 200;
                        $data["msg"] = 'User updated';
                    } else {
                        $data = array("status" => "error", "code" => 400, "msg" => "User not updated");
                    }
                }
            } else {
                $data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid");
            }
            
        }
        return $helpers->json($data);
    }
    
    public function uploadImageAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);
        
        if ($authCheck) {
            $identity = $helpers->authCheck($hash,true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(array (
                "id" => $identity->sub
            ));
            $file = $request->files->get("image");
            if(!empty($file) && $file !=null){
                $ext = $file->guessExtension();
                $file_name = time().".".$ext;
                $file->move("NetB/imgs", $file_name);
                $user->setImage($file_name);
                
                $em->persist($user);
                $em->flush();
                $data = array("status" => "success", "code" => 200, "msg" => "Img uploaded");
            }else{
                $data = array("status" => "error", "code" => 400, "msg" => "Img not uploaded");
            }
        }else {
                $data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid");
            }
            
       return $helpers->json($data);
    }

}
