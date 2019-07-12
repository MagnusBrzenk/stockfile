<?php

namespace curl;

/**
 * Note: Having created these curl methods, I realized that this is a poor way to go about things since it would be much better to first decouple the api routes from their handlers and then invoke these handlers directly server side.
 * That way we don't have needless php -> http -> php communication, and we don't have to deal with server-side authentication. I'm leaving these here as legacy code though since they took some research and debugging, and I'm adding more comments than usual.
 * I'm disabling these routes by inclusion within a if(false) block.
 */

/**
 * For server side php requests to its own API, we'll want to dynamically determine the url to the api.
 */
function getApiUrl($endpoint)
{
    // Build get-caption URL
    $https_prefix = isset($_SERVER['HTTPS']) ? "https://" : "";
    $url = isset($_SERVER['HTTP_HOST']) ? $https_prefix . $_SERVER['HTTP_HOST'] . strstr($_SERVER['REQUEST_URI'], 'stockfile', true) . "stockfile/api/" . $endpoint : null;
    return $url;
}

if (false) {

    function curlGet($endpoint, $headers = NULL)
    {
        // Build url
        $url = getApiUrl($endpoint);
        if (!$url) throw new Error('No API url discernible!');

        // Run curl
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1, // Causes curl_exec() to return string (not just print response)
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => !!$headers ?  array_merge($headers, ['Content-Type:application/json'])  : array('Content-Type:application/json')
        ]);
        $response = curl_exec($curl);
        if (curl_error($curl)) trigger_error('Curl Get Error:' . curl_error($curl));
        curl_close($curl);

        return $response;
    }
    // Test curlGet:
    // curlGet('file/xxx');

    function curlPost($endpoint, $data = NULL, $headers = NULL)
    {
        // Build url
        $url = getApiUrl($endpoint);
        if (!$url) throw new Error('No API url discernible!');

        // Run curl
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => !!$headers ? array_merge($headers, ['Content-Type:application/json']) : array('Content-Type:application/json'),
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $response = curl_exec($curl);
        if (curl_error($curl)) trigger_error('Curl Post Error:' . curl_error($curl));
        curl_close($curl);

        return $response;
    }
    // Test curlPost:
    // curlPost(
    //     'files',
    //     [[
    //         'file_name' => 'yyy',
    //         'exif_created' => '1999-09-09'
    //     ]]
    // );


    function curlPut($endpoint, $data = NULL, $headers = NULL)
    {
        // Build url
        $url = getApiUrl($endpoint);
        if (!$url) throw new Error('No API url discernible!');

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_HTTPHEADER => !!$headers ? array_merge($headers, ['Content-Type:application/json'])  : array('Content-Type:application/json'),
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $response = curl_exec($curl);
        if (curl_error($curl)) trigger_error('Curl Error:' . curl_error($curl));
        curl_close($curl);

        return $response;
    }
    // Test curlPut:
    // echo curlPut(
    //     'files',
    //     [
    //         'file_name' => 'xxx',
    //         'exif_created' => '1801-03-11'
    //     ]
    // );

    function curlDelete($endpoint, $headers = NULL)
    {
        // Build url
        $url = getApiUrl($endpoint);
        if (!$url) throw new Error('No API url discernible!');

        // Run curl
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => !!$headers ? array_merge($headers, ['Content-Type:application/json'])  : array('Content-Type:application/json'),
        ]);
        $response = curl_exec($curl);
        if (curl_error($curl)) trigger_error('Curl Error:' . curl_error($curl));
        curl_close($curl);

        return $response;
    }
    // Test curlDelete:
    // echo curlDelete(
    //     'file/yyy'
    // );
}
