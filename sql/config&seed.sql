create database ecommerce_prova;
use ecommerce_prova;
create table if not exists products
(
    id     int not null auto_increment primary key,
    nome   varchar(50),
    prezzo float,
    marca  varchar(50)
);
insert into products(nome, prezzo, marca)
values ("tosaerba", 289.99, "oleomac"),
       ("vaso", 9.99, "Villeroy e Boch"),
       ("personal computer", 1799.99, "msi"),
       ("bibbia", 15, "san paolo"),
       ("modellino ferrari", 129.99, "Ferrari");