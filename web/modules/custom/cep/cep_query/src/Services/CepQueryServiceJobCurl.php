<?php

namespace Drupal\cep_query\Services;

use Drupal\Core\File\FileSystemInterface;

/**
 * Class CepQueryServiceJobCurl is retrieved data throw curl with proxy.
 */
class CepQueryServiceJobCurl {

  const CACHE_URI = 'public://cep_query_cache/';
  const CACHE_COOK_FILE = 'public://cep_query_cookie/cookies.txt';
  const CACHE_COOK_DIR = 'public://cep_query_cookie/';

  /**
   * {@inheritdoc}
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function getServiceName() {
    return "cep_query.CepQueryServiceJobCurl";
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveData($url, $proxy = "") {
    $ch = curl_init();
    $this->cepProxyCookiePrepareDirectory();
    $this->cepProxyPrepareCacheDirectory();
    file_put_contents(self::CACHE_COOK_FILE, '');
    chmod(self::CACHE_COOK_FILE, 0777);
    curl_setopt($ch, CURLOPT_URL, $url);
    if (!empty($proxy)) {
      curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    if (!is_file('public://cep_proxy_get_html_curl_error.log')) {
      file_put_contents('public://cep_proxy_get_html_curl_error.log', "");
      chmod('public://cep_proxy_get_html_curl_error.log', 0777);
    }
    if (!is_file('public://cep_proxy_get_html_curl.log')) {
      file_put_contents('public://cep_proxy_get_html_curl.log', "");
      chmod('public://cep_proxy_get_html_curl.log', 0777);
    }
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgentRnd());
    curl_setopt($ch, CURLOPT_COOKIEJAR, self::CACHE_COOK_FILE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, self::CACHE_COOK_FILE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $html = curl_exec($ch);
    if (!curl_errno($ch)) {
      switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
        case 200:
          break;

        default:
          file_put_contents('public://cep_proxy_get_html_curl_error.log', $proxy . ';' . md5($url) . ';' . strlen($html) . ';' . date("Y-m-d H:i:s") . ';' . $url);
          return '';
      }
    }
    curl_close($ch);

    file_put_contents('public://cep_proxy_get_html_curl.log', $proxy . ';' . md5($url) . ';' . strlen($html) . ';' . date("Y-m-d H:i:s") . " \n", FILE_APPEND | LOCK_EX);
    
    return $html;

  }

  /**
   * {@inheritdoc}
   */
  private function cepProxyCookiePrepareDirectory() {
    $cookie_uri = self::CACHE_COOK_DIR;
    if (!is_dir(self::CACHE_COOK_DIR)) {
      \Drupal::service('file_system')->prepareDirectory($cookie_uri, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      chmod($cookie_uri, 0777);
    }
  }

  /**
   * {@inheritdoc}
   */
  private function cepProxyPrepareCacheDirectory() {
    $cache_uri_dir = self::CACHE_URI;
    if (!is_dir($cache_uri_dir)) {
      \Drupal::service('file_system')->prepareDirectory($cache_uri_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      chmod($cache_uri_dir, 0777);
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getUserAgentRnd() {
    $user_agent = [
      'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
      'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
      'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36',
      'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
      'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0',
      'Mozilla/5.0 (X11; Linux i586; rv:31.0) Gecko/20100101 Firefox/31.0',
      'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
      'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0',
      'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:42.0) Gecko/20100101 Firefox/42.0',
      'Mozilla/5.0 (Windows NT 5.1; rv:39.0) Gecko/20100101 Firefox/39.0',
      'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 YaBrowser/16.9.1.1192 Yowser/2.5 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
      'Mozilla/5.0 (Windows NT 6.1; rv:40.0) Gecko/20100101 Firefox/40.1',
      'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0',
      'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0 SeaMonkey/2.40',
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10',
      'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
    ];
    $rand_indx = array_rand($user_agent, 1);
    return $user_agent[$rand_indx];
  }

}
