<?php

namespace Drupal\your_module\Plugin\Geocoder\Provider;

use Drupal\geocoder\ConfigurableProviderUsingHandlerWithAdapterBase;
use Geocoder\Provider\ArcGISList\ArcGISList;

/**
 * Geocoder provider plugin for Google Maps for Business.
 *
 * @GeocoderProvider(
 *   id = "arcgislist",
 *   name = "ArcGISListToken",
 *   handler = "\Geocoder\Provider\ArcGISList\ArcGISList",
 *   arguments = {
 *     "client_id" = "",
 *     "client_secret" = "",
 *     "sourceCountry" = "",
 *   }
 * )
 */
class ArcGISListToken extends ConfigurableProviderUsingHandlerWithAdapterBase {

  /**
   * {@inheritdoc}
   */
  protected function getHandler() {
    if ($this->handler === NULL) {
      $token = $this->getToken(
        $this->configuration['client_id'],
        $this->configuration['client_secret']
      );
      if (!$token) {
        throw new InvalidArgument('Token authentication failed.');
      }
      $this->handler = ArcGISList::token(
        $this->httpAdapter,
        $token,
        $this->configuration['sourceCountry']
      );
    }
    return $this->handler;
  }

  /**
   * Get a new or stored token for authenticating to the ArcGIS service.
   */
  private function getToken($client_id, $client_secret) {
    // If the token is cached and not expired, return it.
    $cache = \Drupal::cache()->get('arcgis-service-token');
    if ($cache && $cache->expire > time()) {
      return $cache->data;
    }

    // Get a new token.

    // Inform the user if the token requesting credentials are missing.
    if (!$client_id || !$client_secret) {
      $num = (!$client_id && !$client_secret) ? 'Both' : 'One of the';
      $errors = [
        "{$num} token authentication credentials are missing.",
        'Please contact a developer to have this addressed.'
      ];
      \Drupal::messenger()->addError(implode(' ', $errors));
      return FALSE;
    }
    // Parameters for the authentication endpoint.
    $query = http_build_query([
      'client_id' => $client_id,
      'grant_type' => 'client_credentials',
      'client_secret' => $client_secret,
      'f' => 'json',
    ]);
    $url = "https://www.arcgis.com/sharing/oauth2/token?{$query}";
    try {
      // Query the endpoint.
      $response = \Drupal::httpClient()
        ->get($url, ['headers' => ['Accept' => 'text/plain']]);
      $data = (string) $response->getBody();
      if (empty($data)) {
        \Drupal::messenger()->addError('No query response detected.');
        return FALSE;
      }
      // Decode the JSON response.
      $json = \Drupal\Component\Serialization\Json::decode($data);
      if (!isset($json['access_token']) || !isset($json['expires_in'])) {
        \Drupal::messenger()->addError('Incomplete token received.');
        return FALSE;
      }
      // Set the cache.
      \Drupal::cache()->set(
        'arcgis-service-token',
        $json['access_token'],
        REQUEST_TIME + intval($json['expires_in'])
      );
      // Return the token.
      return $json['access_token'];
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError('An error occurred during the request.');
      return FALSE;
    }
  }

}
