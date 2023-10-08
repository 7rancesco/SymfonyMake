<?php

    namespace App\Service;


    use App\Entity\Connection;
    use Doctrine\ORM\EntityManagerInterface;
    // use Symfony\Component\HttpClient\HttpClient;     /** production ssl */
    use Symfony\Component\HttpClient\CurlHttpClient;    /** dev */    


    class ConnectionService
    {


        public function __construct(

            EntityManagerInterface $em

        ) 
        {
            $this->em     =$em;
        }



        public function getConnection(
            $apiname
        )
        {
            $connection = $this->em->getRepository(Connection::class)->findOneBy(['name' => $apiname]);
            
            $url_login   = $connection->getLogin();
            $user        = $connection->getUser();
            $pass        = $connection->getPass();
            $token       = $connection->getToken();
            $duration    = $connection->getDuration();
            $last_access = $connection->getLastAccess();

            $IS_READY = false;

            if($token AND $last_access)
            {
                if(strtotime('now') - strtotime($last_access->format('Y-m-d H:i:s')) < $duration)
                {
                    $IS_READY = true;
                }
            }

            if($IS_READY)
            {
                return $token;
            }

            else 
            {
                $new_token = $this->getApiToken( $url_login, $user, $pass );

                $connection->setToken($new_token);
                $connection->setLastAccess(new \DateTime());

                $this->em->flush();

                return $new_token;
            }
        }

        public function getApiToken(
            $url_login,
            $user,
            $pass
        )
        {
            // $client = HttpClient::create(); /** production ssl */
            $client = new CurlHttpClient(["verify_peer"=>false,"verify_host"=>false]); /** dev */

            $response = $client->request(
                'POST',
                $url_login,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],    
                    'json' => [
                        "username" => $user,
                        "password" => $pass
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();
            if($statusCode !== 200)
            {
                return false;
            }
            else 
            {
                $contentType = $response->getHeaders()['content-type'][0];
                $content = $response->getContent();    
                $token = json_decode( $content);

                return $token->token;
            }              
        }


        public function useFetch(
            $api_name,
            $method,
            $endpoint,
            $body = false
        )
        {
            $connection = $this->em->getRepository(Connection::class)->findOneBy(['name' => $api_name]);
            $endpoint   = $connection->getPrefix().$endpoint;
            // $client = HttpClient::create(); /** production ssl */
            $client = new CurlHttpClient(["verify_peer"=>false,"verify_host"=>false]); /** dev */  
            
            $response = $client->request(
                $method,
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->getConnection($api_name),
                        'Content-Type'              => 'application/json',
                    ],    
                    'json' => $body ? $body : null,
                ]
            );   
            
            $statusCode = $response->getStatusCode();
            if($statusCode !== 200)
            {
                return $statusCode;
            }
            else 
            {
                $contentType = $response->getHeaders()['content-type'][0];
                $content = $response->getContent();    
                $data = json_decode( $content);

                return $data;
            }             

        }

    }