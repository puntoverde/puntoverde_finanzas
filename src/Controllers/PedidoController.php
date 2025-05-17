<?php

namespace App\Controllers;

use App\DAO\PedidoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class PedidoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getPedidos()
    {
        return PedidoDAO::getPedidos();
    }
    
    public function crearPedido(Request $req)
    {
        $reglas = [
            "id_persona_pedido" => "required", 
            "id_proveedor" => "required",        
            "productos" => "required",        
        ];        

        $this->validate($req, $reglas);
        return PedidoDAO::crearPedido((object)$req->all());
    }


    public function getProductosPedido($id)
    {        
        // dd($id);
        return PedidoDAO::getProductosPedido($id);
    }
   

    public function findProductoDisponiblesRequisicion($id)
    {       
        return PedidoDAO::findProductoDisponiblesRequisicion($id);
    }


    public function agregarProducto($id, Request $req)
    {  
        return PedidoDAO::agregarProducto($id,$req->input('producto'));
    }


    public function eliminarProducto($id)
    {  
        return PedidoDAO::eliminarProducto($id);
    }

    public function pedidosSinRevisar(){
        return PedidoDAO::pedidosSinRevisar();
    }

    public function pedidoRevision($id)
    {       
       return PedidoDAO::pedidoRevision($id);
    }


    public function aceptarProductoPedido(Request $req)
    {
       return PedidoDAO::aceptarProductoPedido((object)$req->all());
    }


    public function rechazarProductoPedido(Request $req)
    {
       return PedidoDAO::rechazarProductoPedido((object)$req->all());
    }


    public function cambioProductoPedido(Request $req)
    {
       return PedidoDAO::cambioProductoPedido((object)$req->all());
    }


    public function cancelarPedido($id)
    {
       return PedidoDAO::cancelarPedido($id);
    }



    public function  finalizarRevisionPedido(Request $req,$id)
    {

        $reglas=[
            "ieps" => "numeric",            
            "iva" => "numeric",
            "subtotal" => "required|numeric", 
            "total" => "required|numeric", 
    ];
    
    $this->validate($req,$reglas);

        return PedidoDAO::finalizarRevisionPedido($id,(object)$req->all());
    }


    public function agregarNotaPedido(Request $req,$id)
    {   

        $name=time();
        
            $file = $req->file('nota_file');            
            $directorio = '../upload/';
            $filename = $name. '.jpeg';
            if ($file->isValid()) {
                try {
                    $file->move($directorio, $filename);
                    return PedidoDAO::agregarNotaPedido($id,$req->input('nota'),$filename);                    
                } catch (\Exception $e) {
                    return $e;
                }
            } 
            else 
              return 0;        

    }


    public function agregarFacturaPdfXml(Request $req,$id)
    {   
        $name=time();
        
        $file = $req->file('factura');
        // $file_xml = $req->file('nota_file');

        $directorio = '../upload/';

        $filename = $name. '.pdf';
        // $filename_xml = $name. '.xml';

            if ($file->isValid() ) {
                try {
                    $file->move($directorio, $filename);
                    return PedidoDAO::agregarFacturaPdfXml($id,$filename);
                } catch (\Exception $e) {
                    return $e;
                }
            }
            else
              return 0;
    }

    public function  cambiarProveedor(Request $req)
    {

        $reglas=[
            "id_pedido_producto" => "required|numeric", 
            "id_proveedor" => "required|numeric", 
    ];
    
    $this->validate($req,$reglas);
       return PedidoDAO::cambiarProveedor($req->input("id_pedido_producto"),$req->input("id_proveedor"));
    }

    public function detalleProductosLibresParaPedido()
    {
        return PedidoDAO::detalleProductosLibresParaPedido();
    }
 
 
    public function cambiarMarcaProductoPedidoRevision(Request $req)
    {
        $id_marca=$req->input("id_marca");
        $id_producto_requisicion=$req->input("id_producto_requisicion");
        return PedidoDAO::cambiarMarcaProductoPedidoRevision($id_producto_requisicion,$id_marca);
    }
    
}
