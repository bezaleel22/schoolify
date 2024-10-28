<?php

namespace Modules\Result\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InterceptStudentView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Intercept the student-view route
        if ($request->routeIs('student_view')) {
            // Perform any logic here (e.g., check conditions)
            // For example, if you want to redirect to the result.student.view route
            return redirect()->route('student_view_detail', [
                'id' => $request->id, // Pass the same parameters
                'type' => $request->type, // Pass the optional parameter
            ]);
        }

        // Continue with the request if no redirection is needed
        return $next($request);
    }
}
