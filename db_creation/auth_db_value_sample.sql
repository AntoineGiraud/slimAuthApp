TRUNCATE TABLE auth_permissions;
TRUNCATE TABLE auth_permission_types;
TRUNCATE TABLE configs;
TRUNCATE TABLE config_types;
TRUNCATE TABLE auth_users;
TRUNCATE TABLE auth_roles;

INSERT INTO `auth_users` (`id`, `email`, `password`, `first_name`, `last_name`, `is_active`) VALUES
(1, 'antoine.giraud@2015.icam.fr', '$2y$10$vF8C9hwamm2/srzgsjI2NOlBk1zHo39g/RGLL.gcum7hB5/s5I9tq', 'Antoine', 'Giraud', 1),
(2, 'user1@operations', 'motdepasse', 'user 1', 'User', 1),
(3, 'user2@entreprise', 'motdepasse', 'user 2', 'User', 1),
(4, 'horsligne@entreprise', 'motdepasse', 'horsligne', 'User', 0);
-- valeurs limitantes possibles pour password : cas_only OR ldap_only

INSERT INTO `auth_roles` (`id`, `slug`, `name`) VALUES
(1, 'superadmin', 'Super administrateur'),
(2, 'member', 'Tout utilisateur'),
(3, 'operations', 'Opérations');

INSERT INTO `auth_permission_types` (`id`, `type`) VALUES
(1, 'role'),
(2, 'user'),
(3, 'user_has_role');

INSERT INTO `config_types` (`id`, `type`) VALUES
(1, 'divers');

INSERT INTO `configs` (`name`, `value`, `type_id`) VALUES
('authentification', '1', 1),
('maintenance', '', 1),
('prevJoursVelosDefectueux', '2', 1),
('seuilVelosDefectueux', '2', 1),
('websitename', 'SlimAuthApp', 1);

INSERT INTO `auth_permissions` (`user_id`, `role_id`, `permission`, `type_id`, `can_access`) VALUES
(1, 1, null, 3, 1),
(2, 3, null, 3, 1),
(1, null, 'superadmin', 2, 1),
(null, 2, '/', 1, 1),
(null, 2, 'about', 1, 1),
(null, 2, 'login', 1, 1),
(null, 2, 'logout', 1, 1),
(null, 2, 'account', 1, 1),
(null, 3, 'operations/*', 1, 1),
(3, null, 'operations/vue_operations', 2, 1);