<?php

    namespace App\Service;

    use App\Entity\User;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    use Symfony\Bundle\SecurityBundle\Security;


    class UserService 
    {
        public function __construct(

            EntityManagerInterface $em,
            UserPasswordHasherInterface $hashPassword,
            Security $security

        )
        {
            $this->hash_password    =$hashPassword;
            $this->em               =$em;
            $this->security         =$security;
        }


        public function hashUserPassword(
            $user,
            $password
        )
        {

            return $this->hash_password->hashPassword(
                $user,
                $password
            );

        }


        public function createUser(

            $username,
            $password,
            $roles

        )
        {

            $username = str_replace(" ","_",$username);
            $username = strtolower($username);

            $user = new User();
    
            $user->setUsername($username);

            $user->setPassword(
                $this->hashUserPassword(
                    $user,
                    $password
                )
            );
            $user->setRoles($roles);

            $this->em->persist($user);
            $this->em->flush();  
            
            return $user;

        }     

        
        public function updateUserPassword(
            $id,
            $password
        )
        {
            $user = $this->findUser($id);

            if($user)
            {


                if($password)
                {
                    $user->setPassword(
                        $this->hashUserPassword(
                            $user,
                            $password
                        )
                    );
                    $this->em->flush();
                }

                return true;

            }
            else 
            {
                return false;
            }
        }        


        public function deleteUser(
            $id
        )
        {
            $user = $this->findUser($id);

            if($user)
            {

                $this->em->remove($user);
                $this->em->flush();

                return true;
            }
            else
            {
                return false;
            }
        }
        
        
        public function findBy(
            $array
        )
        {

            $users = $this->em->getRepository(User::class)->findBy($array);

            if($users)
            {
                return $users;
            }
            else 
            {
                return false;
            }

        }        
        
        public function findUser(
            $id
        )
        {

            $user = $this->em->getRepository(User::class)->find($id);           

            if($user)
            {
                return $user;
            }
            else 
            {
                return false;
            }

        }      

        public function getCurrentUser ()
        {
            
            $user = $this->security->getUser();

            return $user;

        }

    }