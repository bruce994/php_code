:邮件每天最大发送量8000
:php程序中一次数据查询条数100
:for循环  8000/100

set /a max = 16200
set /a limit = 100
set /a a= %max%/%limit%

for /l %%x in (1,1,%a%) do ( 
"D:\programs\php\php.exe" -q "D:\www\misc\test\post02.php"
echo %%x
)
