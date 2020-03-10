<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Models\CL\Author;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/author",
     *     summary="Create new author.",
     *     tags={"CL (Author)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="firstname", description="First name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="lastname", description="Last name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="middlename", description="Middle name", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="about", description="About author", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="avatar", description="Image (max: 2MB)", required=false, in="formData", type="file",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="author",
     *                      @SWG\Items(ref="#/definitions/Author")
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'about' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $data = $request->all();

        $author = new Author();
        $author->fill($data);
        $author->save();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = Storage::disk('cl_author')->putFileAs(
                $author->id, $data['avatar'], 'avatar_' . time() . '.' . $data['avatar']->getClientOriginalExtension()
            );
        }

        $author->update($data);

        return response()->json(['success' => true, 'data' => ['author' => $author]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/cl-moderation/author/{author_id}",
     *     summary="Update author.",
     *     tags={"CL (Author)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="firstname", description="First name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="lastname", description="Last name", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="middlename", description="Middle name", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="about", description="About author", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="avatar", description="Image (max: 2MB)", required=false, in="formData", type="file",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="author",
     *                      @SWG\Items(ref="#/definitions/Author")
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function update(Author $author, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'about' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $data = $request->all();

        if ($request->hasFile('avatar')) {
            Storage::disk('cl_author')->delete($author->avatar);
            $data['avatar'] = Storage::disk('cl_author')->putFileAs(
                $author->id, $data['avatar'], 'avatar_' . time() . '.' . $data['avatar']->getClientOriginalExtension()
            );
        }

        $author->update($data);

        return response()->json(['success' => true, 'data' => ['author' => $author]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/author/{author_id}",
     *     summary="Delete author.",
     *     tags={"CL (Author)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function destroy(Author $author)
    {
        $response = ['success' => true, 'data' => ['message' => 'Author deleted successfully.']];

        try {
            //Storage::disk('cl_author')->deleteDirectory($author->id);
            $author->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);
    }
}
