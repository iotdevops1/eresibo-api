<?php
namespace App\Http\Requests\DocumentVault;
use Illuminate\Foundation\Http\FormRequest;
class UpdateDocumentVaultDocumentRequest extends FormRequest {
    public function authorize():bool{return true;}
    public function rules():array{return ['archived'=>['required','boolean']];}
}
