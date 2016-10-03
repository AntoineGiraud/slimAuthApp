TRUNCATE TABLE auth_permissions;
TRUNCATE TABLE auth_user_has_role;
TRUNCATE TABLE configs;
TRUNCATE TABLE auth_users;
TRUNCATE TABLE auth_roles;

INSERT INTO `auth_users` (`id`, `email`, `password`, `first_name`, `last_name`, `online`) VALUES
(1, 'antoine.giraud@2015.icam.fr', '$2y$10$vF8C9hwamm2/srzgsjI2NOlBk1zHo39g/RGLL.gcum7hB5/s5I9tq', 'Antoine', 'Giraud', 1),
(2, 'user1@operations', 'motdepasse', 'opérations #1', 'User', 1),
(3, 'user2@entreprise', 'motdepasse', 'normal #2', 'User', 1),
(4, 'horsligne@entreprise', 'motdepasse', 'horsligne', 'User', 0);

INSERT INTO `auth_roles` (`id`, `slug`, `name`) VALUES
(1, 'superadmin', 'Super administrateur'),
(2, 'member', 'Tout utilisateur'),
(3, 'operations', 'Opérations');

INSERT INTO `configs` (`name`, `value`) VALUES
('authentification', '1'),
('maintenance', ''),
('prevJoursVelosDefectueux', '2'),
('seuilVelosDefectueux', '2'),
('websitename', 'SlimAuthApp');

INSERT INTO `auth_user_has_role` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 3);

INSERT INTO `auth_permissions` (`user_id`, `role_id`, `permission`, `category`, `allowed`) VALUES
(1, null, 'superadmin', 'user', 1),
(null, 2, '/', 'role', 1),
(null, 2, 'about', 'role', 1),
(null, 2, 'login', 'role', 1),
(null, 2, 'logout', 'role', 1),
(null, 2, 'account', 'role', 1),
(null, 3, 'operations/vue_operations', 'role', 1),
(null, 3, 'operations/vuePersoOperations', 'role', 1),
(3, null, 'operations/vue_operations', 'user', 1);