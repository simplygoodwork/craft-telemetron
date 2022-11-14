/**
 * Telemetron plugin for Craft CMS
 *
 * TelemetronSync Utility JS
 *
 * @author    Good Work
 * @copyright Copyright (c) 2022 Good Work
 * @link      https://simplygoodwork.com
 * @package   Telemetron
 * @since     1.0.0
 */

const button = document.querySelector('#telemetron-sync');

button.addEventListener('click', function(event){
    event.preventDefault();


    Craft.sendActionRequest('POST', 'telemetron/sync/queue-sync', {})
        .then((response) => {
            if(response.status === 200){
                Craft.cp.displayNotice('Sync job added to queue.')
            } else {
                Craft.cp.displayError('Failed to add sync job to queue.')
            }
        })

})