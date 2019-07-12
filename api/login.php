<?php

use db\STOCKFILE_CONFIG;

require_once __DIR__ . "/../utils/db.php";

require_once __DIR__ . '/../vendor/autoload.php';

use \Firebase\JWT\JWT;


function handleHttpRequest()
{
    $request_method = $_SERVER["REQUEST_METHOD"];
    header('Content-Type: application/json'); // Always give json response

    switch ($request_method) {

        case 'GET':
            // The GET case is used to test that the token issued by the POST request works
            $allHeaders = getallheaders();
            $authHeader = isset($allHeaders['Authorization']) ? trim($allHeaders['Authorization']) : null;
            if (!$authHeader) {
                echo json_encode([
                    "status" => 0,
                    "status_message" => "No Authorization Header given!"
                ]);
                return;
            }

            // Parse out token
            $jwt = trim(str_replace('Bearer', '', $authHeader));

            try {

                /*
                 * decode the jwt using the key from config
                 */
                $secretKey = STOCKFILE_CONFIG::$STOCKFILE_SECRET_AUTH_KEY;

                $token = JWT::decode($jwt, $secretKey, array('HS512'));

                print_r([$token]);

                echo json_encode([
                    "status" => 1,
                    "status_message" => "Successful Authorized Request!",
                ]);
                return;
            } catch (Exception $e) {
                /*
                 * the token was not able to be decoded.
                 * this is likely because the signature was not able to be verified (tampered token)
                 */
                header('HTTP/1.0 401 Unauthorized');
                echo json_encode([
                    "status" => 0,
                    "status_message" => "Unsuccessful Attempt At Authorized Request!",
                ]);
                return;
            }


            break;
        case 'POST':

            // Parse post body for username/password; reject if absent
            $POST_BODY = json_decode(file_get_contents("php://input"), true);
            $submitted_username = isset($POST_BODY['username']) ? trim($POST_BODY['username']) : null;
            $submitted_password = isset($POST_BODY['password']) ? trim($POST_BODY['password']) : null;
            if (!$submitted_username || !$submitted_password) {
                echo json_encode([
                    "status" => 0,
                    "status_message" => "Username or password missing"
                ]);
                return;
            }

            // Compare username:password combos with .env entries
            $usernames = STOCKFILE_CONFIG::$STOCKFILE_USER_NAMES;
            $passwords = STOCKFILE_CONFIG::$STOCKFILE_USER_PASSWORDS;
            for ($i = 0; $i < count($usernames); $i++) {

                // Return fresh token if match is made
                if ($usernames[$i] === $submitted_username && $passwords[$i] === $submitted_password) {

                    $tokenId    = base64_encode(random_bytes(32));
                    $issuedAt   = time();
                    $notBefore  = $issuedAt + 10;             //Adding 10 seconds
                    $expire     = $notBefore + 6000;            // Adding 60 seconds
                    $serverName = STOCKFILE_CONFIG::$STOCKFILE_URL; //$config->get('serverName'); // Retrieve the server name from c

                    $token_payload = [
                        'iat'  => $issuedAt,         // Issued at: time when the token was generated
                        'jti'  => $tokenId,          // Json Token Id: a unique identifier for the token
                        'iss'  => $serverName,       // Issuer
                        'nbf'  => $notBefore,        // Not before
                        'exp'  => $expire,           // Expire
                        'data' => [                  // Data related to the signer user
                            'username'   => $submitted_username
                        ]
                    ];

                    $secretKey = STOCKFILE_CONFIG::$STOCKFILE_SECRET_AUTH_KEY;

                    $jwt = JWT::encode(
                        $token_payload,      //Data to be encoded in the JWT
                        $secretKey,                // The signing key
                        'HS512'              // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                    );

                    echo json_encode([
                        "status" => 1,
                        "status_message" => "Successful login",
                        "token" => $jwt
                    ]);
                    return;
                }
            }

            // If no username/password matched, return failure
            echo json_encode([
                "status" => 0,
                "status_message" => "No match for submitted username-password combo"
            ]);
            return;

            break;
        default:
            // Invalid Request Method
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }
}
handleHttpRequest();
