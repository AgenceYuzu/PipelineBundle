# Step 1: Install eZ Publish 5

Follow the [manual installation](https://doc.ez.no/display/EZP/Manual+configuration+of+eZ+Publish) procedure of eZ Publish.


# Step 2: Install ezgmaplocation extension

- Go to the administration interface
- Click on "Setup" (top menu)
- Click on "Extensions" (right menu)
- Check ezgmaplocation checkbox and click on "Update"
- Click on "Regenerate autoload arrays for extensions"
- Apply the ezgmaplocation table to your database with the included sql file: &lt;eZP root&gt;/ezpublish_legacy/extension/ezgmaplocation/sql/mysql/mysql.sql


# Step 3: Install Content Types and Pipeline Demo Content

- Download [pipeline_content_classes-0.1-1.ezpkg](https://github.com/AgenceYuzu/PipelineBundle/blob/7af121b41fbea0263a868b4d170b11f6cd89b755/Resources/packages/pipeline_content_classes-0.1-1.ezpkg?raw=true)
- Download [pipeline_demo_content-0.1-1.ezpkg](https://github.com/AgenceYuzu/PipelineBundle/blob/7af121b41fbea0263a868b4d170b11f6cd89b755/Resources/packages/pipeline_demo_content-0.1-1.ezpkg?raw=true)
- Go to the administration interface
- Click on "Setup" (top menu)
- Click on "Packages" (left menu)
- Click on the button "Import new package"
- Click on "Browse" and select pipeline_content_classes-0.1-1.ezpkg
- Click on "Import package"
- Click on "Install package"
- Click on "Packages" (left menu)
- Click on the button "Import new package"
- Click on "Browse" and select pipeline_demo_content-0.1-1.ezpkg
- Click on "Import package"
- Click on "Install package"
- Place Pipeline Theme for eZ Publish in node Folder eZ Publish (Content Structure)
- Place Icons in node Folder Images (Media Library)
- Place Session in node Folder Images (Media Library)
- Place UI Faces in node Folder Images (Media Library)
- Click on "Next"


# Step 4: Configure rights for Anonymous User
- Go to the administration interface
- Click on "User Accounts" (top menu)
- Click on "Roles and policies" (left menu)
- Click on "Anonymous" and click on "Edit"
- Edit content/read and add "Standard" and "Media" section
- Edit user/login and add the frontend siteaccess
- Click on "Save"


# Step 5: Install Pipeline Bundle

- Add "yuzu/pipelinebundle": "dev-master" in composer.json
- Execute composer update

```bash
$> composer update --no-dev --prefer-dist
```


# Step 6: Configure Pipeline Bundle

- Add new Yuzu\PipelineBundle\YuzuPipelineBundle() in ezpublish/EzPublishKernel.php
- Remove the line "new EzSystemsDemoBundle()" in ezpublish/EzPublishKernel.php
- Update ezpublish.yml

```yml
ezpublish:
    siteaccess:
        group:
            pipeline_frontend_group:
                - &lt;your_siteaccess&gt;
```

```yml
ezpublish:
    &lt;your_siteaccess&gt;:
        content:
            tree_root:
                  # Root locationId of the object Pipeline Theme for eZ Publish [Pipeline Theme] 
                  location_id: 59
```

- Add PipelineBundle routing in ezpublish/config/routing.yml

```yml
_ezpublishPipelineRoutes:
    resource: "@YuzuPipelineBundle/Resources/config/routing.yml"
```

- Remove DemoBundle routing in ezpublish/config/routing.yml

```yml
_ezpublishDemoRoutes:
    resource: "@eZDemoBundle/Resources/config/routing.yml"
```

- Update assetic parameters in ezpublish/config/config.yml

```yml
assetic:
    debug:          "%kernel.debug%"
    use_controller: false
    bundles:        [ YuzuPipelineBundle ]
```
 
- Dump assetic
 
```bash
$> php ezpublish/console assets:install --symlink web
$> php ezpublish/console assetic:dump --env=dev web
$> php ezpublish/console assetic:dump --env=prod web
```
 
- Clear cache

```bash
$> php ezpublish/console cache:clear
```

- Enjoy!