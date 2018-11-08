<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** ====================
 *  offer.controller.php
 *  ====================
 *
 *  api.deviantspy.com/offer/
 *
 */


switch($controllerFunction)
{
    /** ============================
     *  http://api.deviantspy.com/offer/add
     */
    case 'add':

    break;

    default:


        api_response(array(
            'code'=> RESPONSE_ERROR,
            'data'=> array(
                'message'=>'Not a valid controller object.'
            )
        ));
    break;

}
