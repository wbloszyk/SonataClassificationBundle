API
===

SonataClassificationBundle embeds a controller to provide a ReST API through FOSRestBundle, with its documentation provided by NelmioApiDocBundle.

Setup
-----

If you wish to use it, you must first follow the installation instructions of both bundles:

* `FOSRestBundle <https://github.com/FriendsOfSymfony/FOSRestBundle>`_
* `NelmioApiDocBundle <https://github.com/nelmio/NelmioApiDocBundle>`_

Here's the configuration we used, you may adapt it to your needs:

.. code-block:: yaml

    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        format_listener: true
        view:
            view_response_listener: force
        body_converter:
            enabled: true
            validate: true

    sensio_framework_extra:
        router: { annotations: true }
        request: { converters: true }
        format_listener:
            rules:
                - { path: '^/', priorities: ['json'], fallback_format: json, prefer_extension: false }

    twig:
        exception_controller: null

    framework:
        error_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'

In order to activate the APIs, you'll also need to add this to your routing:

.. code-block:: yaml

    NelmioApiDocBundle:
        prefix: /api/doc
        resource: "@NelmioApiDocBundle/Resources/config/routing.yml"

    sonata_api_classification:
        prefix: /api/classification
        resource: "@SonataClassificationBundle/Resources/config/routing/api.xml"

    # or for nelmio/api-doc-bundle v3
    #sonata_api_classification:
    #    prefix: /api/classification
    #    resource: "@SonataClassificationBundle/Resources/config/routing/api_nelmio_v3.xml"

Serialization
-------------

We're using JMSSerializationBundle's serialization groups to customize the inputs and outputs.

The taxonomy is as follows:
* ``sonata_api_read`` is the group used to display entities
* ``sonata_api_write`` is the group used for input entities (when used instead of forms)

If you wish to customize the outputted data, feel free to set up your own serialization options by configuring JMSSerializer with those groups.
