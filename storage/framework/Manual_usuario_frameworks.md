## Instalación de Composer para la creación del Proyecto Compareware

1.- Busca Composer ene el navegador de tu gusto 
![alt text](imagenes/image-2.png)
 
2. Ingresa al sitio en la pestaña de download
 
![alt text](imagenes/image-3.png)

3. Descarga la versión mas reciente para tu sistema operativo (en mi caso Windows)
![alt text](imagenes/image-4.png)
 
4. Instala Composer en tu unidad de preferencia (Mi caso disco c: )
![alt text](imagenes/image-5.png)
 
5.- Finaliza la instalación para posteriormente acceder al cmd y comprobar su instalación, sabemos que fue una instalación exitosa si al ingresar el comando Composer arroja la palabra composer y su versión 
 ![alt text](imagenes/image-6.png)



## Instalación de Laravel para la creación del proyecto Compareware

1.	Al instalar composer se nos agrega una carpeta llamada XAMPP en el disco donde seleccionamos que se instalara Composer, esta carpeta tiene carpetas mas  entre ellas la llamada htdocs en donde haremos una nueva carpeta, esta carpeta será la de nuestro poryecto y será llamada Compareware.

![alt text](imagenes/image-7.png)
 
2.	Una vez creada la carpeta instalaremos Laravel usando el comando "composer global require laravel/insatler"
 
![alt text](imagenes/image-8.png)

3.	 Una vez instalado laravel crearemos el proyecto compareware con el comando  composer global require "laravel/instaler", una vez ejecutado este empezara a crear todos los documentos necesarios para poder crear un proyecto basado en la arquitectura mvc.

![alt text](imagenes/image-9.png)

## Instalacion de Node.js para el proyecto Compareware

1-	Buscamos Node.js en nuestro navegador de confianza 
 
 ![alt text](imagenes/image-10.png)

2.-  En la pestaña descarga buscamos la versión y el sistema operativo para poderlo instalar 
 ![alt text](imagenes/image-11.png)
 
3 - Dentro de la carpeta Xampp del disco c, buscaremos la carpeta htdocs y crearemos una nueva carpeta (en mi caso llamada JavaS) esta carpeta servirá para hacer la instalación de node.js 
 
 ![alt text](imagenes/image-12.png)

4- Abriremos la terminal en la ruta de la carpeta JavaS creada anteriormente, para posteriormente ejecutar los comandos 
mkdir api-node
cd api-node
npm init -y
npm install express cors body-parser

![alt text](imagenes/image-13.png)
 
Una vez concluidas las instalaciones podemos empezar a codificar y diseñar nuestro proyecto, haciendo conexiones a base de datos y desarrollar el frontend y backend 😊