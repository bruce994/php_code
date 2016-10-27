:邮件每天最大发送量500
:php程序中一次数据查询条数100
:for循环  500/100

set /a max = 500
set /a limit = 100
set /a a= %max%/%limit%

for /l %%x in (1,1,%a%) do ( 
"C:\Program Files\PHP\php.exe" -q "C:\www\test\post01.php"
echo %%x
)
