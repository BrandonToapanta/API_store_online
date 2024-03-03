drop database examen_movil;

create database examen_movil;
use examen_movil;

create table cliente (
    id int primary key auto_increment,
    clien_nombre varchar(60) not null,
    clien_apellido varchar(60) not null,
    clien_edad int not null,
    clien_genero varchar(10) not null,
    clien_cedula_ruc varchar(13) not null,
    clien_telefono varchar(10) not null,
    clien_direccion varchar(150) not null,
    clien_correo varchar(150) not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

create table producto(
  id int primary key auto_increment,
  prod_codigo varchar(10) not null,
  prod_nombre varchar(50) not null,
  prod_descripcion varchar(250) not null,
  prod_precio varchar(10) not null,
  prod_stok int not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

create table pedido(
  id int primary key auto_increment,
  id_cliente int,
  id_producto int,
  ped_codigo varchar(10) not null,
  ped_numero varchar(10) not null,
  ped_fecha date not null,
  ped_estado varchar(20) not null,
  FOREIGN KEY (id_cliente) REFERENCES cliente(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

create table detalle_pedido(
  id int primary key auto_increment,
  id_pedido int,
  detal_codigo varchar(10) not null,
  detal_cantidad int not null,
  detal_precio_unitario varchar(10) not null,
  detal_precio_asociado varchar(10) not null,
  FOREIGN KEY (id_pedido) REFERENCES pedido(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


