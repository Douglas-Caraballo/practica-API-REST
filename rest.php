<?php

// Autenticacion HTTP
/*
$user = array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
$pwd = array_key_exists('PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : '';

//Esto es una prueba nunca lo hagas
if($user !== 'dou'|| $pwd !== '1234'){

    die;
}*/

// Autenticacion HMAC

if( !array_key_exists('HTTP_X_HASH',$_SERVER) ||
    !array_key_exists('HTTP_X_TIMESTAMP',$_SERVER) ||
    !array_key_exists('HTTP_X_UID',$_SERVER)
){
    die;
}

list($hash, $uid, $timestamp)=[
    $_SERVER['HTTP_X_HASH'],
    $_SERVER['HTTP_X_UID'],
    $_SERVER['HTTP_X_TIMESTAMP'],
];

$secret= 'Sh!! No se lo cuentes a nadie!';

$newHash = sha1($uid.$timestamp.$secret);

if($newHash !== $hash){
    die;
}


// Autenticacion Access Tokens
/*if(!array_key_exists('HTTP_X_TOKEN',$_SERVER)){
    die;
}

$url ='http://localhost:8001';

$ch = curl_init($url);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    [
        "X_Token:{$_SERVER['HTTP_X_TOKEN']}"
    ]
    );
curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);

$ret=curl_exec($ch);

if($ret !=='true'){
    die;
}
*/
// Definimos los recursos disponibles
$allwedResourceType = [
    'books',
    'authors',
    'genre'
];

// Validamos que el recurso este disponible
$resuorceType = $_GET['resource_type'];

if(!in_array($resuorceType, $allwedResourceType)){

    die;
}

//Defino los recursos
$books=[
    1=>[
        'titulo' => 'Lo que el viento se llevo',
        'id_autor' => 2,
        'id_genero' => 2
    ],
    2=>[
        'titulo' => 'La Ileada',
        'id_autor' => 1,
        'id_genero' => 1
    ],
    3=>[
        'titulo' => 'La Odisea',
        'id_autor' => 1,
        'id_genero' => 1
    ]
];

header('Content-Type: application/json');

//Levantamos el id del recurso buscado
$resourceId = array_key_exists('resource_id',$_GET) ? $_GET['resource_id'] : '';

// Generamos la respuesta asumiendo que el pedido es correcto
switch(strtoupper($_SERVER['REQUEST_METHOD'])){
    case 'GET':
        if(empty($resourceId)){
            echo json_encode($books);
        }else{
            if(array_key_exists($resourceId, $books) ){
                echo json_encode($books[$resourceId]);
            }
        }

        break;
    case 'POST':
        //toma las entradas curdas
        $json = file_get_contents('php://input');

        //Transforma el json recibido a un nievo elemento del array
        $books[]= json_decode($json, true);

        //Emitimos hacia la salida la ultima clave del arreglo
        echo array_keys($books)[count($books)-1];

        //echo json_encode($books);
        break;
    case 'PUT':
        //validamos que el recurso buscado exista
        if(!empty($resourceId) && array_key_exists($resourceId, $books)){
            //Tomamos la entrada cruda
            $json = file_get_contents('php://input');

            //Transforma el json recibido a un nievo elemento del array
            $books[$resourceId]= json_decode($json, true);

            //Retornamos la coleccion modificada en formato json
            echo json_encode($books);
        }
        break;
    case 'DELETE':
        //validamos que el recurso exista
        if(!empty($resourceId) && array_key_exists($resourceId, $books)){
            //eliminamos el recurso
            unset($books[$resourceId]);
        }

        echo json_encode($books);
        break;
}