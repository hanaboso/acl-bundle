Hanaboso AclBundle
=====================

[![Build Status](https://travis-ci.org/hanaboso/acl-bundle.svg?branch=master)](https://travis-ci.org/hanaboso/acl-bundle)
[![Coverage Status](https://coveralls.io/repos/github/hanaboso/acl-bundle/badge.svg?branch=master)](https://coveralls.io/github/hanaboso/acl-bundle?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://img.shields.io/badge/PHPStan-level%208-brightgreen)
[![Downloads](https://img.shields.io/packagist/dt/hanaboso/acl-bundle)](https://packagist.org/packages/hanaboso/acl-bundle)

Installation
-----------
* Download package via composer
```bash
composer require hanaboso/acl-bundle
```

## Resources
All resources & actions protected by ACL must by registered via enum and symfony parameters
Configuration below shows registration of both Resource and Action enum, together with marking db documents beloging to given resources.

acl_use_cache allows caching with redis so that rules doesn't have to be loaded from db every time.

```
parameters:
    resource_enum: Hanaboso\AclBundle\Enum\ResourceEnum
    action_enum: AclBundleTests\testApp\ExtActionEnum
    acl_use_cache: true

    db_res:
        resources:
            # Add new resources to ResourceEnum class
            user: Hanaboso\UserBundle\Document\User
            tmp_user: Hanaboso\UserBundle\Document\TmpUser
            token: Hanaboso\UserBundle\Document\Token
            file: Hanaboso\CommonsBundle\FileStorage\Document\File
            group: Hanaboso\AclBundle\Document\Group
            rule: Hanaboso\AclBundle\Document\Rule

    # Optionals - can be empty: ~
    resource_actions:
        # [read, write, delete] by default (set in MaskFactory)
        default_actions: ['read', 'write', 'delete', 'test']
        # specific actions on top of default ones
        resources:
            token: ['test2']

```

resource_actions is option parameter that allows extending default ['read', 'write', 'delete'] actions. Only up to 32 different actions is allowed.

## Rules

Rules are defined in two separate groups. Standard and Owner's rules.

Owner rules are applied only if object contains owner property and it's Id matches with logged user. 

Rules set under fixture_groups are global and apply to all instances regardless of ownership. Each rule has:
 - level: priority of group. If ACL rules and groups are editable from users, each user can edit only itself & lower priorities (protects superadmin from admin with lower priority)
 - extends: includes rules from specified groups
 - users: pre-generated users
 - rules: specifies each resource with all rules allowed for given group

```
parameters:
    acl_rule:
        owner:
            # Key must match with key in acl.yml under resources
            user:   ['read', 'write']
            group:  ['read', 'write']

        fixture_groups:
            admin:
                level: 1
                extends:        ['user', 'test']
                users:
                    - {email: 'root@hanaboso.com', password: 'root'}
                rules:
                    group:      ['read']
                    user:       ['read', 'write', 'delete']
                    tmp_user:   ['read', 'write', 'delete']
                    token:      ['read', 'write']
                    topology:   ['read', 'write']
                    node:       ['read', 'write']
                    file:       ['read', 'write']
            user:
                level: 5
                extends:        ['test']
                users:
                rules:
                    topology:   ['read']
                    node:       ['read']
                    file:       ['read']
```

## Entities/Documents 

AclBundle is dependant on UserBundle and both it's entities/documents must be registered to doctrine.

ORM mappings
```
UserEntity:
    type: annotation
    is_bundle: false
    dir: "%src_dir%/src/Entity"
    prefix: Hanaboso\UserBundle\Entity
AclEntity:
    type: annotation
    is_bundle: false
    dir: "%src_dir%/src/Entity"
    prefix: Hanaboso\AclBundle\Entity
```

ODM mappings
```
UserDocument:
    type: annotation
    is_bundle: false
    dir: "%src_dir%/src/Document"
    prefix: Hanaboso\UserBundle\Document
AclDocument:
    type: annotation
    is_bundle: false
    dir: "%src_dir%/src/Document"
    prefix: Hanaboso\AclBundle\Document
```

## Usage in code

Checking rules for given user is done via AccessManager's method isAllowed(string $action, string $resource, UserInterface $user, $object = NULL)

Request action & resource is validated against enums registered above. UserInterface is taken from UserBundle and represents logged user. Object is optional parameter of object or it's Id.

Examples
```
isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser);
isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, '1258');
isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, $resource);
```

Usages of object parameter:
- NULL -> check if $user has permission for Write or GroupPermission for Read & Delete
    isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser);
    returns TRUE if allowed or throws an exception

- string -> id of desired entity
    isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, '1258');
    returns desired entity if found and user has permission for asked action or throws an exception

- object -> check permission for given entity
    isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, $something);
    returns back given object or throws an exception

- other formats like array or int will only throws an exception

## Generation of groups & rules
All required entities/documents are generated via fixtures.
After creating a new rule, it can be added with fixtures as well as it checks uniqueness. 
