--
-- PostgreSQL database dump
--

CREATE TABLE cliente (
    codigo integer,
    nome character varying(40),
    telefone character varying(20),
    endereco character varying(40),
    idade integer,
    foto character varying(40),
    ref_cidade integer
);


CREATE TABLE produto (
    codigo integer,
    descricao character varying(40),
    unidade character(2),
    estoque numeric(14,2),
    preco_custo numeric(14,2),
    preco_venda numeric(14,2)
);


CREATE TABLE vendas (
    ref_cliente integer,
    ref_produto integer,
    quantidade numeric(14,2),
    data date,
    preco numeric(14,2)
);


CREATE TABLE estado (
    codigo character(2),
    descricao character varying(40)
);

CREATE TABLE cidade (
    codigo integer,
    descricao character varying(40),
    ref_estado character(2)
);

INSERT INTO cliente VALUES (8, 'Tiago Giovanaz', '55 3443-3458', 'Av. dos Giovanaz', 23, 'images/photos/tiago.png', 5);
INSERT INTO cliente VALUES (13, 'William Prigol Lopes', '55 3434-4545', 'Rua Gunsnroses', 19, 'images/photos/william.png', 5);
INSERT INTO cliente VALUES (12, 'Henrique Gravina', '55 3434-1212', 'Rua Hidraulica', 17, 'images/photos/henrique.png', 1);
INSERT INTO cliente VALUES (11, 'Jamiel Spezia', '55 3434-0404', 'Rua dos gringos', 17, 'images/photos/jamiel.png', 5);
INSERT INTO cliente VALUES (10, 'Nasair da Silva', '55 3434-3454', 'Av. Frederico Hulck', 19, 'images/photos/nasair.png', 1);
INSERT INTO cliente VALUES (9, 'Alexandre Schmidt', '55 3434-5656', 'Av. General Neto', 25, 'images/photos/xande.png', 4);
INSERT INTO cliente VALUES (7, 'Vilson Cristiano Gärtner', '55 3434-5234', 'Rua Avelino Talini', 30, 'images/photos/vilson.png', 1);
INSERT INTO cliente VALUES (5, 'Daniel Afonso Heisler', '55 3434-2342', 'Av. 28 de Maio', 23, 'images/photos/daniel.png', 2);
INSERT INTO cliente VALUES (4, 'João Alex Fritsch', '55 3434-5445', 'Rua Benjamin Constant', 29, 'images/photos/joao.png', 1);
INSERT INTO cliente VALUES (3, 'Pablo DallOglio', '55 3434-9595', 'Rua Conceicao', 23, 'images/photos/pablo.png', 3);
INSERT INTO cliente VALUES (2, 'Cesar Brod', '55 3434-5535', 'Rua Julio de Castilhos', 38, 'images/photos/cesar.png', 1);
INSERT INTO cliente VALUES (1, 'Maurício de Castro', '55 3434-9876', 'Rua Bento Goncalves', 26, 'images/photos/mauricio.png', 1);
INSERT INTO cliente VALUES (15, 'Ana Paula Araujo', '55 3434-9393', 'Av. Sao Rafael', 23, 'images/photos/ana.png', 3);
INSERT INTO cliente VALUES (16, 'Armando Taffarel', '55 3434-2424', 'Av. Internacional', 20, 'images/photos/armando.png', 1);
INSERT INTO cliente VALUES (18, 'Diego Bienchetti', '55 3434-1814', 'Av. Bianchetti', 24, 'images/photos/diego.png', 1);
INSERT INTO cliente VALUES (19, 'Douglas Scheibler', '55 3434-2822', 'Av. Teutonia', 24, 'images/photos/douglas.png', 1);
INSERT INTO cliente VALUES (20, 'Ana Paula Fieguenbaum', '55 3434-3332', 'Av. Figui', 24, 'images/photos/figui.png', 1);
INSERT INTO cliente VALUES (21, 'Janaina Bald', '55 3433-3332', 'Av. Figui', 24, 'images/photos/jana.png', 2);
INSERT INTO cliente VALUES (22, 'Jessica Käfer', '55 3443-3332', 'Av. dos Kafer', 21, 'images/photos/jessica.png', 2);
INSERT INTO cliente VALUES (23, 'Josi Petter', '55 3443-6632', 'Av. Estrelense', 24, 'images/photos/josi.png', 2);
INSERT INTO cliente VALUES (24, 'Junior Mulinari', '55 3443-7738', 'Av. Vespasiano', 25, 'images/photos/junior.png', 1);
INSERT INTO cliente VALUES (25, 'Marcone Theisen', '55 3443-1128', 'Av. Alemao', 25, 'images/photos/marcone.png', 1);
INSERT INTO cliente VALUES (28, 'Paulo Köetz', '55 3443-2298', 'Av. dos Koetz', 23, 'images/photos/paulo.png', 1);
INSERT INTO cliente VALUES (27, 'Rudi Uhrig Neto', '55 3443-9868', 'Av. dos Uhrig', 23, 'images/photos/rudi.png', 1);
INSERT INTO cliente VALUES (26, 'Maico Schmitz', '55 3443-3318', 'Av. Meiense', 25, 'images/photos/maico.png', 1);
INSERT INTO cliente VALUES (14, 'Joice Käfer', '55 3434-2344', 'Av. Gumercindo Saraiva', 21, 'images/photos/joice.png', 1);
INSERT INTO cliente VALUES (17, 'Diego Bellin', '55 3434-1414', 'Av. Chevrolet', 24, 'images/photos/bellin.png', 1);


INSERT INTO produto VALUES (1, 'chocolate', 'OZ', 100.00, 2.00, 2.50);
INSERT INTO produto VALUES (3, 'notepad', 'PC', 1400.00, 9.00, 12.00);
INSERT INTO produto VALUES (5, 'pencil', 'PC', 800.00, 1.80, 1.10);
INSERT INTO produto VALUES (6, 'soap', 'OZ', 200.00, 0.10, 0.20);
INSERT INTO produto VALUES (7, 'refrigerant', 'PC', 400.00, 1.20, 1.50);
INSERT INTO produto VALUES (8, 'wine bottle', 'PC', 250.00, 4.60, 6.00);
INSERT INTO produto VALUES (9, 'orange', 'OZ', 150.00, 1.70, 2.30);
INSERT INTO produto VALUES (10, 'strawberry', 'OZ', 600.00, 0.90, 1.20);
INSERT INTO produto VALUES (11, 'pineapple', 'OZ', 400.00, 1.80, 2.20);
INSERT INTO produto VALUES (12, 'pants', 'PC', 300.00, 39.00, 52.00);
INSERT INTO produto VALUES (13, 'tshirt', 'PC', 500.00, 19.00, 34.90);
INSERT INTO produto VALUES (14, 'shoe', 'PC', 300.00, 29.00, 49.90);
INSERT INTO produto VALUES (15, 'mouse', 'PC', 200.00, 9.00, 19.90);
INSERT INTO produto VALUES (16, 'access point', 'PC', 100.00, 25.00, 39.90);



INSERT INTO vendas VALUES (7, 1, 7.00, '2003-10-31', 23.00);
INSERT INTO vendas VALUES (9, 1, 3.00, '2003-10-31', 23.00);
INSERT INTO vendas VALUES (1, 2, 2.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (2, 2, 2.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (4, 2, 5.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (6, 2, 4.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (8, 2, 2.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (1, 3, 9.00, '2003-10-31', 11.99);
INSERT INTO vendas VALUES (3, 3, 4.00, '2003-10-31', 11.99);
INSERT INTO vendas VALUES (5, 3, 7.00, '2003-10-31', 13.00);
INSERT INTO vendas VALUES (7, 3, 4.00, '2003-10-31', 13.00);
INSERT INTO vendas VALUES (9, 3, 6.00, '2003-10-31', 11.99);
INSERT INTO vendas VALUES (2, 4, 2.00, '2003-10-31', 0.50);
INSERT INTO vendas VALUES (4, 4, 7.00, '2003-10-31', 0.50);
INSERT INTO vendas VALUES (6, 4, 7.00, '2003-10-31', 0.59);
INSERT INTO vendas VALUES (8, 4, 7.00, '2003-10-31', 0.59);
INSERT INTO vendas VALUES (10, 4, 4.00, '2003-10-31', 0.59);
INSERT INTO vendas VALUES (1, 5, 4.00, '2003-10-31', 10.00);
INSERT INTO vendas VALUES (3, 5, 4.00, '2003-10-31', 10.00);
INSERT INTO vendas VALUES (5, 5, 3.00, '2003-10-31', 10.00);
INSERT INTO vendas VALUES (7, 5, 7.00, '2003-10-31', 10.00);
INSERT INTO vendas VALUES (9, 5, 9.00, '2003-10-31', 10.00);
INSERT INTO vendas VALUES (2, 6, 9.00, '2003-10-31', 0.20);
INSERT INTO vendas VALUES (4, 6, 2.00, '2003-10-31', 0.20);
INSERT INTO vendas VALUES (6, 6, 9.00, '2003-10-31', 0.20);
INSERT INTO vendas VALUES (10, 6, 9.00, '2003-10-31', 0.29);
INSERT INTO vendas VALUES (2, 8, 3.00, '2003-10-31', 20.00);
INSERT INTO vendas VALUES (2, 8, 7.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (4, 8, 3.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (6, 8, 2.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (8, 8, 2.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (10, 8, 8.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (1, 12, 4.00, '2003-10-31', 21.00);
INSERT INTO vendas VALUES (1, 13, 4.00, '2003-10-31', 13.99);
INSERT INTO vendas VALUES (2, 14, 2.00, '2003-10-31', 11.99);
INSERT INTO vendas VALUES (4, 16, 3.00, '2003-10-31', 16.00);
INSERT INTO vendas VALUES (3, 15, 6.00, '2003-10-31', 5.00);
INSERT INTO vendas VALUES (1, 1, 2.00, '2003-10-31', 23.99);
INSERT INTO vendas VALUES (3, 1, 42.00, '2003-10-31', 23.99);
INSERT INTO vendas VALUES (5, 1, 7.00, '2003-10-31', 23.99);
INSERT INTO vendas VALUES (10, 2, 9.00, '2003-10-31', 15.00);
INSERT INTO vendas VALUES (1, 7, 3.00, '2003-10-31', 15.00);
INSERT INTO vendas VALUES (3, 7, 7.00, '2003-10-31', 15.00);
INSERT INTO vendas VALUES (5, 7, 7.00, '2003-10-31', 15.00);
INSERT INTO vendas VALUES (7, 7, 4.00, '2003-10-31', 15.00);
INSERT INTO vendas VALUES (9, 7, 4.00, '2003-10-31', 15.00);


INSERT INTO estado VALUES ('RS', 'Rio Grande do Sul');
INSERT INTO estado VALUES ('SP', 'São Paulo');
INSERT INTO estado VALUES ('RJ', 'Rio de Janeiro');
INSERT INTO estado VALUES ('MG', 'Minas Gerais');


INSERT INTO cidade VALUES (1, 'Porto Alegre', 'RS');
INSERT INTO cidade VALUES (2, 'Lajeado', 'RS');
INSERT INTO cidade VALUES (3, 'Santa Clara', 'RS');
INSERT INTO cidade VALUES (4, 'Estrela', 'RS');
INSERT INTO cidade VALUES (5, 'Batatais', 'SP');
INSERT INTO cidade VALUES (6, 'Ribeirão Preto', 'SP');
INSERT INTO cidade VALUES (7, 'São Caetano', 'SP');
INSERT INTO cidade VALUES (8, 'Santos', 'SP');
INSERT INTO cidade VALUES (9, 'Nova Friburgo', 'RJ');
INSERT INTO cidade VALUES (10, 'Ilhéus', 'RJ');
INSERT INTO cidade VALUES (11, 'Búzios', 'RJ');
INSERT INTO cidade VALUES (12, 'Niterói', 'RJ');
INSERT INTO cidade VALUES (13, 'Ipatinga', 'MG');
INSERT INTO cidade VALUES (14, 'Montes Claros', 'MG');
INSERT INTO cidade VALUES (15, 'Juiz de Fora', 'MG');
INSERT INTO cidade VALUES (16, 'Poços de Caldas', 'MG');


