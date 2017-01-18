<?php

namespace AppBundle\Services;
use Firebase\JWT\JWT;
class JwtAuth {
    public $manager;
    public $key;
    public function __construct($manager){
        $this->manager = $manager;
        $this->key = "Correct pass";
    }
    
    public function signup ($email, $password, $getHash=null){
        $key = $this->key;
        $user = $this->manager->getRepository("BackendBundle:User")->findOneBy(
                array ("email"=>$email,"password"=>$password)
                );
        $sigup = false;
        if(is_object($user)){
            $sigup=true;
        }
        if($sigup==true){
            $token = array (
                "sub"=>$user->getId(),
                "email"=>$user->getEmail(),
                "name"=>$user->getName(),
                "surname"=>$user->getSurname(),
                "password"=>$user->getPassword(),
                "image" =>$user->getImage(),
                "iat" =>time(),
                "exp"=>time() + (7*24*60*60)
            );
            $jwt = JWT::encode($token, $key, 'HS256');
            $decode = JWT::decode($jwt, $key, array('HS256'));
            
            if($getHash!=null){
                return $jwt;
            }else {
                return $decode;
            }
        }else{
            return array ("status"=>"error", "data"=>"login failed");
        }
    }
    
    public function checkToken($jwt, $getIdentity = false){
        $key = $this->key;
        $auth = false;
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
        }catch(\UnexpectedValueException $e){
            $auth = false;
        } catch (\DomainException $e){
            $auth = false;
        }
        if(isset($decoded->sub)){
            $auth = true;
        } else {
            $auth = false;
        }
        if($getIdentity==true){
            return $decoded;
        }else{
            return $auth;
        }
    }
}
