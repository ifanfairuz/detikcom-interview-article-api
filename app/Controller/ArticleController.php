<?php

namespace App\Controller;

use App\Repository\Article;
use App\Validation\Validation;
use Core\Http\Exception\HttpException;
use Core\Http\Request;

class ArticleController extends Controller
{
    /**
     * create article
     * 
     */
    public function create(Request $req)
    {
        $input = $req->only(['title', 'summary', 'position', 'author']);

        // validate input
        $validation = new Validation([
            'title' => 'required|string|max_length[100]',
            'summary' => 'required|string|max_length[500]',
            'position' => 'required|integer|between[1,5]',
            'author' => 'required|string|max_length[100]',
        ]);
        $validation->validate($input);

        // insert into database
        try {

            $repo = new Article();
            $res = $repo->insert($input);
            if ($res) {
                $res['created_at'] = date_format(date_create($res['created_at']), 'Y-m-d H:i:s');
                return $this->jsonResponse($res);
            }

            return $this->response()->error($req, new HttpException());
        } catch (\Exception $e) {
            throw $e;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * update article
     * 
     */
    public function update(Request $req)
    {
        $article_id = $req->input('article_id');
        $input = $req->only(['title', 'summary', 'position', 'author']);

        $validateData = $input;
        $validateData['article_id'] = $article_id;

        // validate input
        $validation = new Validation([
            'article_id' => 'required|integer|max_length[11]',
            'title' => 'required|string|max_length[100]',
            'summary' => 'required|string|max_length[500]',
            'position' => 'required|integer|between[1,5]',
            'author' => 'required|string|max_length[100]',
        ]);
        $validation->validate($validateData);

        // update data on database
        try {
            $repo = new Article();
            $res = $repo->updateWithPrimaryKey($input, $article_id);
            if ($res) {
                $res['article_id'] = $article_id;
                $res['updated_at'] = date_format(date_create($res['updated_at']), 'Y-m-d H:i:s');
                return $this->jsonResponse($res);
            }

            return $this->jsonResponse([
                'status' => 404,
                'message' => "No record with article_id " . $article_id
            ]);
        } catch (\Exception $e) {
            throw $e;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
