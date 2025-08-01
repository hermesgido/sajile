<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\PostVote;
use App\Models\PostImage;
use App\Models\VoteCredit;
use App\Models\CommentVote;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use App\Models\UserNotification;
use App\Models\PostCommentReport;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_type' => 'required|in:text,job,event',
            'title' => 'required|regex:/^[a-z\-_\s]+$/i',
            'category' => 'required',
            'salary' => 'required_if:post_type,job',
            'content' => 'required|string',
            'images.*' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png']), 'max:2048'],
            'deadline' => 'required_if:post_type,job',
            'vacancy' => 'numeric|required_if:post_type,job',
            'start_date' => 'string|required_if:post_type,event',
            'end_date' => 'string|required_if:post_type,event',
            'participant' => 'numeric|required_if:post_type,event',
        ]);

        if ($request->category) {
            $findCategory = Category::where('id', $request->category)->first();
            if ($findCategory == null) {
                $notify[] = ['error', 'Category is not Valid'];
                return back()->withNotify($notify);
            }
        }

        $post = new Post();
        $purifier = new \HTMLPurifier();

        if ($request->post_type == "text") {
            $post->type = 'text';
        } elseif ($request->post_type == "job") {
            if (auth()->user()->credit < gs()->credit) {
                $notify[] = ['error', 'Your account not enough credit'];
                return back()->withNotify($notify);
            }

            $user = User::find(auth()->id());
            $user->credit = $user->credit - gs()->credit;
            $user->save();

            $post->type = 'job';
            $post->salary = $request->salary;
            $post->deadline = $request->deadline;
            $post->vacancy = $request->vacancy;
        } elseif ($request->post_type == "event") {
            $start_date = Carbon::createFromFormat('Y-m-d H:i', Str::replace('T', ' ', $request->start_date));
            $end_date = Carbon::createFromFormat('Y-m-d H:i', Str::replace('T', ' ', $request->end_date));
            if ($start_date > $end_date) {
                $notify[] = ['error', 'Your end date is not valid'];
                return back()->withNotify($notify);
            }
            if (auth()->user()->credit < gs()->event_credit) {
                $notify[] = ['error', 'Your account not enough credit'];
                return back()->withNotify($notify);
            }
            $user = User::find(auth()->id());
            $user->credit = $user->credit - gs()->event_credit;
            $user->save();

            $post->type = 'event';
            $post->start_date = $start_date;
            $post->end_date = $end_date;
            $post->participant = $request->participant;
            $post->fee = $request->fee;
        }
        $post->user_id = auth()->user()->id;
        $post->category_id = $request->category ?? 0;
        $post->title = $request->title;
        $post->content = $purifier->purify($request->content);
        $post->status = 1;
        $post->updated_at = null;
        $post->save();
        if ($request->post_type == "text" || $request->post_type == "event") {
            if ($request->hasFile('images')) {
                try {
                    foreach ($request->images as $image) {
                        $post_image = new PostImage();
                        $post_image->post_id = $post->id;
                        $post_image->user_id = auth()->id();
                        $post_image->path = '/' . date("Y") . '/' . date("m") . '/';
                        $post_image->image = fileUploader($image, getFilePath('posts') . $post_image->path, getFileSize('posts'));
                        $post_image->save();
                    }
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Couldn\'t upload your image'];
                    return back()->withNotify($notify);
                }
            }
        }

        $notify[] = ['success', 'Post Create successfully'];
        return back()->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit posts';
        $categories = Category::all();
        $post = Post::with('images')->where('id', $id)->first();
        $post->updated_at = null;
        $post->save();
        if ($post->type == 'job') {
            return view($this->activeTemplate . 'job-post.edit-post', compact('pageTitle', 'post','categories'));
        } elseif ($post->type == 'event') {
            return view($this->activeTemplate . 'event-post.edit-post', compact('pageTitle', 'post', 'categories'));
        } else {
            return view($this->activeTemplate . 'text-post.edit-post', compact('pageTitle', 'post', 'categories'));
        }
    }

    public function update(Request $request, $id)
    {
        $pageTitle = 'Update posts';
        $request->validate([
            'post_type' => 'required|in:text,job,event',
            'title' => 'required|regex:/^[a-z\-_\s]+$/i',
            'category' => 'required_if:post_type,text',
            'salary' => 'required_if:post_type,job',
            'content' => 'required|string',
            'images.*' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png']), 'max:2048'],
            'deadline' => 'required_if:post_type,job',
            'vacancy' => 'numeric|required_if:post_type,job',
            'start_date' => 'string|required_if:post_type,event',
            'end_date' => 'string|required_if:post_type,event',
            'participant' => 'numeric|required_if:post_type,event',
        ]);

        if ($request->category) {
            $findCategory = Category::where('id', $request->category)->first();
            if ($findCategory == null) {
                $notify[] = ['error', 'Category is not Valid'];
                return back()->withNotify($notify);
            }
        }
        $purifier = new \HTMLPurifier();
        $post = Post::findOrFail($id);
        $post->user_id = auth()->user()->id;
        if ($request->post_type == "text") {
            $post->type = 'text';
        } elseif ($request->post_type == 'job') {
            $post->salary = $request->salary;
            $post->deadline = $request->deadline;
            $post->vacancy = $request->vacancy;
        } elseif ($request->post_type == "event") {

            $start_date = Carbon::createFromFormat('Y-m-d H:i', Str::replace('T', ' ', $request->start_date));
            $end_date = Carbon::createFromFormat('Y-m-d H:i', Str::replace('T', ' ', $request->end_date));
            if ($start_date > $end_date) {
                $notify[] = ['error', 'Your end date is not valid'];
                return back()->withNotify($notify);
            }

            $post->type = 'event';
            $post->start_date = $start_date;
            $post->end_date = $end_date;
            $post->participant = $request->participant;
            $post->fee = $request->fee;
        }

        if ($request->post_type == "text" || $request->post_type == "event") {
            if ($request->hasFile('images')) {
                try {
                    foreach ($request->images as $image) {
                        $post_image = new PostImage();
                        $post_image->post_id = $post->id;
                        $post_image->user_id = auth()->id();
                        $post_image->path = '/' . date("Y") . '/' . date("m") . '/';
                        $post_image->image = fileUploader($image, getFilePath('posts') . $post_image->path, getFileSize('posts'));
                        $post_image->save();
                    }
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Couldn\'t upload your image'];
                    return back()->withNotify($notify);
                }
            }
        }

        $post->user_id = auth()->user()->id;
        $post->category_id = $request->category ?? 0;
        $post->title = $request->title;
        $post->content = $purifier->purify($request->content);
        $post->status = 1;
        $post->updated_at = now()->format('Y-m-d h:i:s');
        $post->save();

        $notify[] = ['success', 'Post Update successfully'];
        return redirect()->route('home')->withNotify($notify);
    }

    public function job_list(Request $request)
    {
        $pageTitle = 'job posts';
        $emptyMessage = 'No job post found';
        $jobs = Post::where('user_id', auth()->id())->where('type', 'job')->orderBy('id', 'desc')->paginate(getPaginate());
        if ($request->search) {
            $jobs = Post::where('title', 'like', "%$request->search%")->orderBy('id', 'desc')->paginate(getPaginate());
        }
        $user = User::where('id', auth()->user()->id)->with('posts.comments')->first();
        return view($this->activeTemplate . 'user.my-jobs.job-list', compact('pageTitle', 'jobs', 'emptyMessage', 'user'));
    }

    public function post_vote(Request $request)
    {
        $data =  $request->validate([
            'post_id' => 'required|numeric',
            'vote' => 'required|in:1,0',
        ]);

        $post_vote = new PostVote();
        $vote_credit = new VoteCredit();
        $post = Post::with('user')->where('id', $request->post_id)->first();
        $exist_vote =  $post_vote->where('post_id', $request->post_id)->where('user_id', auth()->id())->first();

        $exist_vote_credit =  $vote_credit->where('post_id', $request->post_id)->where('user_id', auth()->user()->id)->first();

        // User notification
        $userNotification = new UserNotification();
        $userNotification->user_from = auth()->id();
        $userNotification->user_to = $post->user->id;
        $userNotification->title = (!$exist_vote) ? auth()->user()->fullname . ' like your post ' . $post->title : auth()->user()->fullname . ' Unlike your post ' . $post->title;
        $userNotification->read_status = 0;
        $userNotification->type = 'post-vote';
        $userNotification->click_url = url('/') . '/details/' . $post->id . '/' . slug($post->title);
        $userNotification->save();

        // credit plus when user upvote his post
        if ($request->vote == 1 && !$exist_vote_credit) {
            $vote_credit->user_id = auth()->id();
            $vote_credit->post_id = $request->post_id;
            $vote_credit->save();

            $user = User::findOrFail($post->user?->id);

            $user->credit += gs()->upvote_credit;
            $user->save();
        }

        if (!$exist_vote) {
            $post_vote->post_id = $request->post_id;
            $post_vote->user_id = auth()->user()->id;
            $post_vote->type = auth()->user()->type;
            if ($request->vote == 1) {
                $post_vote->like = 1;
            } else {
                $post_vote->unlike = 1;
            }
            $post_vote->save();
            $data = $this->total_like_unlike_count($post_vote, $request);


            return response()->json($data);
        } else {
            if ($exist_vote->like == 1 && $request->vote == 1) {
                $exist_vote->delete();
                $data = $this->total_like_unlike_count($post_vote, $request);
                return response()->json($data);
            }

            if ($exist_vote->like == 1 && $request->vote == 0) {
                $exist_vote->like = 0;
                $exist_vote->unlike = 1;
                $exist_vote->save();
                $data = $this->total_like_unlike_count($post_vote, $request);
                return response()->json($data);
            }

            if ($exist_vote->unlike == 1 && $request->vote == 0) {
                $exist_vote->delete();
                $data = $this->total_like_unlike_count($post_vote, $request);
                return response()->json($data);
            }

            if ($exist_vote->unlike == 1 && $request->vote == 1) {
                $exist_vote->like = 1;
                $exist_vote->unlike = 0;
                $exist_vote->save();

                $data = $this->total_like_unlike_count($post_vote, $request);
                return response()->json($data);
            }
        }
    }

    public function post_bookmark(Request $request)
    {
        $data =  $request->validate([
            'post_id' => 'required|numeric',
        ]);

        $post = Post::where('id', $request->post_id)->first();
        $bookmark = new Bookmark();
        $exist_bookmark =  $bookmark->where('post_id', $request->post_id)->where('user_id', auth()->user()->id)->first();

        if (!$exist_bookmark) {
            $bookmark->post_id = $request->post_id;
            $bookmark->user_id = auth()->user()->id;
            $bookmark->type = auth()->user()->type;
            $bookmark->save();
            $data = [
                'status' => "saved",
                'message' => "Post saved successfully",
                'id' => $request->post_id,
            ];

            return response()->json($data);
        }
        $exist_bookmark->delete();
        $data = [
            'status' => "unsaved",
            'message' => "Post unsaved successfully",
            'id' => $request->post_id,
        ];
        return response()->json($data);
    }

    public function post_report(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'post_id' => 'required|numeric',
        ]);
        $post_report = new PostCommentReport();
        $exist_post_report = $post_report->where('post_report_user', auth()->id())->where('type', 'post')->where('post_id', $request->post_id)->first();

        if ($exist_post_report) {
            $data = [
                'status' => "error",
                'message' => "You are already report this post",
            ];
            return response()->json($data);
        }

        $post = Post::where('id', $request->post_id)->with('user')->first();
        $post_report->post_id = $post->id;
        $post_report->user_id = $post->user->id;
        $post_report->type = 'post';
        $post_report->post_report_user = auth()->id();
        $post_report->reason =  $request->reason;
        $post_report->save();

        // User notification

        $userNotification = new UserNotification();
        $userNotification->user_from = auth()->id();
        $userNotification->user_to = $post->user->id;
        $userNotification->title = auth()->user()->fullname . ' report your post ' . $post->title;
        $userNotification->read_status = 0;
        $userNotification->type = 'post-report';
        $userNotification->click_url = url('/') . '/details/' . $post->id . '/' . slug($post->title);
        $userNotification->save();

        $data = [
            'status' => "success",
            'message' => "Reported successfully",
        ];
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
        ]);
        $posts = Post::where('title', 'like', "%$request->search%")->get();

        $data = [
            'status' => "success",
            'data' => $posts,
        ];
        return response()->json($data);
    }

    public function postImageDelete(Request $request)
    {
        try {
            $post_image = PostImage::findOrFail($request->id);
            fileManager()->removeFile(getFilePath('posts') . $post_image->path . $post_image->image);
            $post_image->delete();
            $data = [
                'status' => "success",
                'message' => "Image deleted successfully",
            ];
            return response()->json($data);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Couldn\'t delete your image'];
            return back()->withNotify($notify);
        }
    }

    private function total_like_unlike_count($post_vote, $request)
    {
        $total_like = $post_vote->where('post_id', $request->post_id)->where('like', 1)->count();
        $total_unlike = $post_vote->where('post_id', $request->post_id)->where('unlike', 1)->count();
        return number_format_short($total_like - $total_unlike);
    }

    public function postStatus(Request $request)
    {
        $data = Post::where('id', $request->id)->first();
        $data->status = $this->statusCheck($data);
        return response()->json(
            $data = [
                'status' => "success",
                'message' => "Post status updated"
            ],
        );
    }

    private function statusCheck($data)
    {
        if ($data->status === 1) {
            $data->status = 0;
            $data->save();
        } elseif ($data->status === 0) {
            $data->status = 1;
            $data->save();
        }
        return $data;
    }
}
