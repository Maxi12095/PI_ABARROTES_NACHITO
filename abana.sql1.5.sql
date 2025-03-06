-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-02-2025 a las 15:29:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `abana`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddCategory` (IN `category_name` VARCHAR(255))   BEGIN
    INSERT INTO categories (Category_Name)
    VALUES (category_name);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddProduct` (IN `p_Product` VARCHAR(255), IN `p_Description` TEXT, IN `p_Price` DECIMAL(10,2), IN `p_Qty` INT, IN `p_Fk_Category` INT, IN `p_Sku` VARCHAR(255))   BEGIN
    -- Inserción de un nuevo producto en la tabla
    INSERT INTO products (Product, Description, Price, Qty, fk_category, SKU)
    VALUES (p_Product, p_Description, p_Price, p_Qty, p_Fk_Category, p_Sku);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddSecurityQuestion` (IN `question_text` VARCHAR(255))   BEGIN
    INSERT INTO security_questions (Question_Text) VALUES (question_text);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddUser` (IN `firstname_input` VARCHAR(50), IN `lastname_input` VARCHAR(50), IN `username_input` VARCHAR(50), IN `password_input` VARCHAR(255), IN `role_input` INT)   BEGIN
    INSERT INTO users (FirstName, LastName, Username, Password, FK_Role)
    VALUES (firstname_input, lastname_input, username_input, password_input, role_input);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteSecurityQuestion` (IN `question_id` INT)   BEGIN
    DELETE FROM security_questions
    WHERE ID_Question = question_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteUser` (IN `user_id` INT)   BEGIN
    DELETE FROM users WHERE ID_Users = user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `EditUser` (IN `user_id` INT, IN `first_name` VARCHAR(100), IN `last_name` VARCHAR(100), IN `username` VARCHAR(100), IN `password` VARCHAR(255), IN `role_id` INT)   BEGIN
    UPDATE users
    SET 
        FirstName = first_name,
        LastName = last_name,
        Username = username,
        Password = password,
        FK_Role = role_id
    WHERE ID_Users = user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAnswerHashesByUser` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        a.Answer_Hash 
    FROM 
        user_security_answers AS a
    JOIN 
        users AS u ON a.FK_User = u.ID_Users
    WHERE 
        u.Username = p_username
    ORDER BY 
        a.FK_Question ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCategories` ()   BEGIN
    SELECT ID_Category, Category_Name FROM categories;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPasswordByUser` (IN `userID` INT)   BEGIN
    SELECT Password FROM users WHERE ID_Users = userID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProductById` (IN `product_id` INT)   BEGIN
    SELECT 
        ID_Product,
        Product,
        Description,
        Price,
        Qty,
        fk_category,
        SKU
    FROM 
        products
    WHERE 
        ID_Product = product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProductsByCategory` (IN `category_id` INT)   BEGIN
    SELECT 
        p.ID_Product, 
        p.Product, 
        p.Description, 
        p.Price, 
        p.Qty, 
        c.Category_Name AS Category
    FROM products p
    JOIN categories c ON p.Category_ID = c.ID_Category
    WHERE c.ID_Category = category_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetSecurityQuestions` ()   BEGIN
    SELECT ID_Question, Question_Text FROM security_questions;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetSecurityQuestionsByUser` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        q.ID_Question, 
        q.Question_Text 
    FROM 
        user_security_answers AS a
    JOIN 
        security_questions AS q ON a.FK_Question = q.ID_Question
    JOIN 
        users AS u ON a.FK_User = u.ID_Users
    WHERE 
        u.Username = p_username
    ORDER BY 
        q.ID_Question ASC; -- Asegúrate de que esto esté presente
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUsers` ()   BEGIN
    SELECT ID_Users, CONCAT(FirstName, ' ', LastName) AS FullName, Username
    FROM users;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserSecurityQuestions` (IN `p_username` VARCHAR(255))   BEGIN
    SELECT 
        q.ID_Question, q.Question_Text 
    FROM 
        user_security_answers AS a
    JOIN 
        security_questions AS q ON a.FK_Question = q.ID_Question
    JOIN 
        users AS u ON a.FK_User = u.ID_Users
    WHERE 
        u.Username = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertUserSecurityAnswer` (IN `p_FK_User` INT, IN `p_FK_Question` INT, IN `p_Answer_Hash` VARCHAR(255))   BEGIN
    INSERT INTO user_security_answers (FK_User, FK_Question, Answer_Hash)
    VALUES (p_FK_User, p_FK_Question, p_Answer_Hash);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RecoverPassword` (IN `user_name` VARCHAR(100), IN `question_id` INT, IN `user_answer` VARCHAR(255), OUT `user_password` VARCHAR(255))   BEGIN
    DECLARE stored_hash VARCHAR(255);
    
    -- Obtener el hash de la respuesta almacenada
    SELECT Answer_Hash INTO stored_hash
    FROM user_security_answers
    INNER JOIN users ON user_security_answers.FK_User = users.ID_Users
    WHERE users.Username = user_name AND user_security_answers.FK_Question = question_id;
    
    -- Comparar la respuesta ingresada con el hash almacenado
    IF stored_hash IS NOT NULL AND PASSWORD(user_answer) = stored_hash THEN
        SELECT Password INTO user_password
        FROM users
        WHERE Username = user_name;
    ELSE
        SET user_password = NULL; -- Si falla, no devuelve nada
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RecoverPasswordMultiple` (IN `user_name` VARCHAR(100), IN `question1_id` INT, IN `answer1` VARCHAR(255), IN `question2_id` INT, IN `answer2` VARCHAR(255), IN `question3_id` INT, IN `answer3` VARCHAR(255), OUT `user_password` VARCHAR(255))   BEGIN
    DECLARE hash1 VARCHAR(255);
    DECLARE hash2 VARCHAR(255);
    DECLARE hash3 VARCHAR(255);

    -- Obtener los hashes de las respuestas de las 3 preguntas
    SELECT Answer_Hash INTO hash1
    FROM user_security_answers
    INNER JOIN users ON users.ID_Users = user_security_answers.FK_User
    WHERE users.Username = user_name AND user_security_answers.FK_Question = question1_id;

    SELECT Answer_Hash INTO hash2
    FROM user_security_answers
    INNER JOIN users ON users.ID_Users = user_security_answers.FK_User
    WHERE users.Username = user_name AND user_security_answers.FK_Question = question2_id;

    SELECT Answer_Hash INTO hash3
    FROM user_security_answers
    INNER JOIN users ON users.ID_Users = user_security_answers.FK_User
    WHERE users.Username = user_name AND user_security_answers.FK_Question = question3_id;

    -- Validar las respuestas ingresadas
    IF PASSWORD(answer1) = hash1 AND PASSWORD(answer2) = hash2 AND PASSWORD(answer3) = hash3 THEN
        -- Si todas las respuestas coinciden, devolver la contraseña
        SELECT Password INTO user_password
        FROM users
        WHERE Username = user_name;
    ELSE
        -- Si alguna respuesta falla, no devolver nada
        SET user_password = NULL;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchProductById` (IN `product_id` INT)   BEGIN
    SELECT 
        p.ID_Product,
        p.Product,
        p.Description,
        p.Price,
        p.Qty,
        c.Category_Name AS Category
    FROM 
        products p
    JOIN 
        categories c ON p.fk_category = c.ID_Category
    WHERE 
        p.ID_Product = product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchProductByIdAndCategory` (IN `product_id` INT, IN `category_id` INT)   BEGIN
    -- Si se pasa el ID del producto y el ID de la categoría
    IF product_id IS NOT NULL AND category_id IS NOT NULL THEN
        SELECT 
            p.ID_Product,
            p.Product,
            p.Description,
            p.Price,
            p.Qty,
            c.Category_Name AS Category
        FROM 
            products p
        JOIN 
            categories c ON p.fk_category = c.ID_Category
        WHERE 
            p.ID_Product = product_id
        AND 
            p.fk_category = category_id;

    -- Si solo se pasa el ID del producto
    ELSEIF product_id IS NOT NULL THEN
        SELECT 
            p.ID_Product,
            p.Product,
            p.Description,
            p.Price,
            p.Qty,
            c.Category_Name AS Category
        FROM 
            products p
        JOIN 
            categories c ON p.fk_category = c.ID_Category
        WHERE 
            p.ID_Product = product_id;

    -- Si solo se pasa el ID de la categoría
    ELSEIF category_id IS NOT NULL THEN
        SELECT 
            p.ID_Product,
            p.Product,
            p.Description,
            p.Price,
            p.Qty,
            c.Category_Name AS Category
        FROM 
            products p
        JOIN 
            categories c ON p.fk_category = c.ID_Category
        WHERE 
            p.fk_category = category_id;

    -- Si no se pasa ningún parámetro, se traen todos los productos
    ELSE
        SELECT 
            p.ID_Product,
            p.Product,
            p.Description,
            p.Price,
            p.Qty,
            c.Category_Name AS Category
        FROM 
            products p
        JOIN 
            categories c ON p.fk_category = c.ID_Category;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchProductByName` (IN `search_input` VARCHAR(100))   BEGIN
    SELECT 
        p.ID_Product,         -- ID del producto
        p.Product,            -- Nombre del producto
        p.Description,        -- Descripción del producto
        c.Category_Name AS Category, -- Nombre de la categoría (relación con categories)
        p.Price,              -- Precio del producto
        p.Qty                 -- Cantidad del producto
    FROM 
        products p
    LEFT JOIN categories c ON p.fk_category = c.ID_Category -- Relación con la tabla categories
    WHERE 
        p.Product LIKE CONCAT('%', search_input, '%');      -- Permite buscar parcialmente por nombre
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchProductBySku` (IN `p_Sku` VARCHAR(255))   BEGIN
    SELECT * FROM products
    WHERE SKU LIKE CONCAT('%', p_Sku, '%');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateCategory` (IN `category_id` INT, IN `category_name` VARCHAR(255))   BEGIN
    UPDATE categories
    SET Category_Name = category_name
    WHERE ID_Category = category_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdatePassword` (IN `userID` INT, IN `newPasswordHash` VARCHAR(255))   BEGIN
    -- Asegurar que la nueva contraseña sea diferente de la actual
    IF EXISTS (SELECT 1 FROM users WHERE ID_Users = userID AND Password = newPasswordHash) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La nueva contraseña no puede ser igual a la actual.';
    ELSE
        UPDATE users SET Password = newPasswordHash WHERE ID_Users = userID;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdatePasswordByUsername` (IN `p_username` VARCHAR(255), IN `p_hashed_password` VARCHAR(255))   BEGIN
    UPDATE users
    SET Password = p_hashed_password
    WHERE Username = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProduct` (IN `p_product` VARCHAR(255), IN `p_description` TEXT, IN `p_price` DECIMAL(10,2), IN `p_qty` INT, IN `p_fk_category` INT, IN `p_sku` VARCHAR(255), IN `p_product_id` INT)   BEGIN
    UPDATE products
    SET
        Product = p_product,
        Description = p_description,
        Price = p_price,
        Qty = p_qty,
        fk_category = p_fk_category,
        SKU = p_sku -- Actualización del SKU
    WHERE ID_Product = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateSecurityQuestion` (IN `question_id` INT, IN `question_text` VARCHAR(255))   BEGIN
    UPDATE security_questions
    SET Question_Text = question_text
    WHERE ID_Question = question_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserPassword` (IN `user_id` INT, IN `new_password_hash` VARCHAR(255))   BEGIN
    UPDATE users 
    SET Password = new_password_hash
    WHERE ID_Users = user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UserLogin` (IN `username_input` VARCHAR(50))   BEGIN
    SELECT 
        ID_Users,
        Username,
        Password,
        FK_Role
    FROM 
        users
    WHERE 
        Username = username_input;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerifyCurrentPassword` (IN `user_id` INT)   BEGIN
    SELECT Password 
    FROM users 
    WHERE ID_Users = user_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `ID_Category` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`ID_Category`, `Category_Name`) VALUES
(1, 'Frutas'),
(2, 'Verduras'),
(3, 'Dulces'),
(4, 'Refrescos'),
(5, 'Bebidas energeticas'),
(6, 'Limpieza'),
(7, 'Enlatados'),
(8, 'Papas Fritas'),
(9, 'Jugos');

--
-- Disparadores `categories`
--
DELIMITER $$
CREATE TRIGGER `ReuseCategoryID` BEFORE INSERT ON `categories` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    -- Buscar el menor ID disponible
    SELECT MIN(ID_Category + 1) INTO next_id
    FROM categories
    WHERE (ID_Category + 1) NOT IN (SELECT ID_Category FROM categories);
    
    -- Si hay un ID disponible, usarlo
    IF next_id IS NOT NULL THEN
        SET NEW.ID_Category = next_id;
    ELSE
        -- Si no hay ID reutilizable, usar el siguiente ID mayor
        SET NEW.ID_Category = (SELECT IFNULL(MAX(ID_Category), 0) + 1 FROM categories);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `ID_Product` int(11) NOT NULL,
  `Product` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Qty` int(11) NOT NULL,
  `fk_category` int(11) NOT NULL,
  `SKU` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`ID_Product`, `Product`, `Description`, `Price`, `Qty`, `fk_category`, `SKU`) VALUES
(1, 'Manzana Roja', 'Una manzana', 4.00, 3, 1, 455),
(2, 'Chicles', 'unos simples chicles', 1.00, 3, 3, 3),
(3, 'pepsi 600ml', 'pepsi 600ml', 18.00, 6, 4, 3520),
(4, 'Fabuloso lavanda 2L', 'Fabuloso lavanda 2L', 22.00, 34, 6, 0),
(5, 'Powerade moras 600ml', 'Powerade moras 600ml', 24.00, 2, 5, 0),
(6, 'Atun en agua lata', 'Atun en agua lata', 21.00, 3, 7, 0),
(7, 'Sabritas orignales', 'Sabritas orignales', 25.00, 5, 8, 0),
(8, 'Jumex 600ml', 'Jumex 600ml', 23.00, 5, 9, 0),
(9, 'Fanta 3 Litros', 'Fanta 3 Litros desechable ', 4388.00, 5, 4, 0),
(10, 'COCA COLA 600ML', 'COCA COLA 600ML DESECHABLE ', 20.00, 4, 4, 0),
(11, 'SALSA VALENTINA', 'SALSA VALENTINA 300GR', 20.00, 6, 5, 0),
(12, 'SALSA VALENTINA', 'SALSA VALENTINA 300GR', 43.00, 4, 6, 0),
(13, 'Jugo del Valle lata 455ml', 'Jugo del Valle lata 455ml Mango', 13.00, 22, 9, 0),
(14, 'Jarritos 600ml', 'Jarritos 600ml Ponche', 111.00, 234, 4, 0),
(15, 'platano', 'platano', 4.00, 33, 2, 23432);

--
-- Disparadores `products`
--
DELIMITER $$
CREATE TRIGGER `ReuseProductID` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    -- Buscar el menor ID disponible
    SELECT MIN(ID_Product + 1) INTO next_id
    FROM products
    WHERE (ID_Product + 1) NOT IN (SELECT ID_Product FROM products);
    
    -- Si hay un ID disponible, usarlo
    IF next_id IS NOT NULL THEN
        SET NEW.ID_Product = next_id;
    ELSE
        -- Si no hay ID reutilizable, usar el siguiente ID mayor
        SET NEW.ID_Product = (SELECT IFNULL(MAX(ID_Product), 0) + 1 FROM products);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `IDRole` int(11) NOT NULL,
  `Role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`IDRole`, `Role_name`) VALUES
(1, 'administrador'),
(2, 'empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales`
--

CREATE TABLE `sales` (
  `ID_Sales` int(11) NOT NULL,
  `Users` int(11) NOT NULL,
  `Products` int(11) NOT NULL,
  `Sales_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_questions`
--

CREATE TABLE `security_questions` (
  `ID_Question` int(11) NOT NULL,
  `Question_Text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `security_questions`
--

INSERT INTO `security_questions` (`ID_Question`, `Question_Text`) VALUES
(1, '¿Cuál es el nombre de tu primera mascota?'),
(2, '¿En qué ciudad naciste?'),
(3, '¿Cuál es tu comida favorita?'),
(4, '¿Cómo se llama tu mejor amigo de la infancia?'),
(5, '¿Cuál era el nombre de tu primera escuela?'),
(6, '¿Cuál es tu película favorita?'),
(7, '¿Qué apodo te ponían de pequeño?'),
(8, '¿Cuál es tu videojuego favorito?'),
(9, '¿Cuál es tu artista favorito?');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `ID_Users` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `FK_Role` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`ID_Users`, `FirstName`, `LastName`, `Username`, `Department`, `Password`, `FK_Role`) VALUES
(1, 'Artyom', 'Wilhelm', 'artyom', 'TI', '$2y$10$IQu90ExdOYVeHDYQpknox.K95Uwpd6MT/LDa6HrschFPsvAi67hMi', 1),
(2, 'admin', 'poderoso', 'admin', NULL, '$2y$10$3f6HHmfbfUPwWmcaUme6S.b0kpTAValYOVcPLHTOMRsNPOjdbh3la', 1),
(3, 'Max', 'Gonzalez', 'max', NULL, '$2y$10$wM/sUsoDVwe.mGzoup6/EuSAC6m90CbGHLxuOSbBBWpwA667TLTyG', 1),
(4, 'Jose', 'Perales', 'perales', NULL, '$2y$10$TaHm3e5Yh5gUr6Y6aBUI3.rTW9SQl5Rm7Rk/CrMUlw.QeDb48rsIe', 2),
(5, 'Daniel', 'Ruiz', 'daniel.ruiz', NULL, '$2y$10$pXkE5c1aiRTqlyDfbZvNMusuQ5P8YTxF6sLRQFECpbNmBnxYtY.hi', 1),
(6, 'Sonia', 'Lopez', 'So.nia', NULL, '$2y$10$KZkwj7FHwpdoq94Jk/nKd.fFobGoV0AgZBcwYNsfiS4laRiVP2bZa', 1),
(7, 'Neftali', 'Santes', 'nef.tali', NULL, '$2y$10$Nt5PRZkMw1dt/gUhcbVDHu1LoLOCNEP7ytlthS1w3oPFC2SNPuyLG', 2),
(8, 'Enrique', 'Galvan', 'enri.que', NULL, '$2y$10$BwacqZV1A8wOiI3aGJY8AedgYR0GFkoYPYEXGeReKplLNe.WJUayG', 2),
(10, 'Samuel', 'Oviedo', 'samu.el', NULL, '$2y$10$1FN6c8mOE4F0psqC3NSMEeUtaXKtzpCVcnEw2C/NrLoZq/Kwk/cgS', 2),
(11, 'Juan', 'Pérez', 'juanperez', NULL, '$2y$10$z2.bfiveyoSmt3tcUCKyuOuDea3V89RQs6ZGdvX0FTG6SpZutDDiC', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_security_answers`
--

CREATE TABLE `user_security_answers` (
  `ID_Answer` int(11) NOT NULL,
  `FK_User` int(11) NOT NULL,
  `FK_Question` int(11) NOT NULL,
  `Answer_Hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_security_answers`
--

INSERT INTO `user_security_answers` (`ID_Answer`, `FK_User`, `FK_Question`, `Answer_Hash`) VALUES
(16, 1, 2, '$2y$10$Nze.2oRsdigNNMDytXiF..fE5S17wnjKb/BcVPakHHp9aYsc2nRPO'),
(17, 1, 6, '$2y$10$93gzjw.Aof35Q4Grso5CpOlJMi3B8AivNL5Vgu729nqviww1C4QHS'),
(18, 1, 8, '$2y$10$bGKV2CP11CzssYCdAw2IkOJcOSN5tcnVZjJ5xooCFzqTLBWUrapYi'),
(19, 7, 2, '$2y$10$pGJiIlMswQfOnKjykZIsEuZCIIVkgXJ8UBq3mC9bNSjKpmlJANOoG'),
(20, 7, 5, '$2y$10$sSywSKZTOlzG7HYxIiJ93.U9rfMYlC.Lw5NJLlSUz.AgodM3cxy46'),
(21, 7, 9, '$2y$10$WAY.egrGvlhO7ZQdicMMd.uI0m7dHI74i64xF6wpUOojqmzw70ZqO'),
(22, 8, 2, '$2y$10$nuE1rXlogPuzvp/.vadVxe8Rl8VczR8axbBB2mPec1MpTRRxXXUDO'),
(23, 8, 6, '$2y$10$a2OY3ezRCHsGlvzAsEyAauDEDFFkl48xm7cOoafIAhoODnDT4TICy'),
(24, 8, 9, '$2y$10$YkDEpLgB4gisdGzEd94cq.xo3Vbp.dcZVHxSZiIZvCr5JdTMNcps6'),
(25, 10, 2, '$2y$10$mhGHSicylR93C8x7tkyGauuD1OsxTOuUd4n7bcYNptWKgrjRVLexO'),
(26, 10, 5, '$2y$10$Zkb50G5g9AzyIaM.9GkqK.NJvucZ8uynoh0VZ3vh6v9cWYJZZYHHu'),
(27, 10, 8, '$2y$10$meg/UoSc1f0UIvGmS5z27.YrqJ2BdTP31Nz0gvLGzadF/026evFZm');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`ID_Category`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID_Product`),
  ADD KEY `products_categories_FK` (`fk_category`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`IDRole`);

--
-- Indices de la tabla `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`ID_Sales`),
  ADD KEY `Users` (`Users`),
  ADD KEY `Products` (`Products`);

--
-- Indices de la tabla `security_questions`
--
ALTER TABLE `security_questions`
  ADD PRIMARY KEY (`ID_Question`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_Users`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `FK_Role` (`FK_Role`);

--
-- Indices de la tabla `user_security_answers`
--
ALTER TABLE `user_security_answers`
  ADD PRIMARY KEY (`ID_Answer`),
  ADD KEY `FK_User` (`FK_User`),
  ADD KEY `FK_Question` (`FK_Question`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `IDRole` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `ID_Sales` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `ID_Question` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `ID_Users` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user_security_answers`
--
ALTER TABLE `user_security_answers`
  MODIFY `ID_Answer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_categories_FK` FOREIGN KEY (`fk_category`) REFERENCES `categories` (`ID_Category`);

--
-- Filtros para la tabla `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`Users`) REFERENCES `users` (`ID_Users`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`Products`) REFERENCES `products` (`ID_Product`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`FK_Role`) REFERENCES `roles` (`IDRole`);

--
-- Filtros para la tabla `user_security_answers`
--
ALTER TABLE `user_security_answers`
  ADD CONSTRAINT `user_security_answers_ibfk_1` FOREIGN KEY (`FK_User`) REFERENCES `users` (`ID_Users`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_security_answers_ibfk_2` FOREIGN KEY (`FK_Question`) REFERENCES `security_questions` (`ID_Question`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
