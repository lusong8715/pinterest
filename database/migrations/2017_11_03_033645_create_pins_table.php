<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pin_id', 64);
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('product_sku', 128)->nullable();
            $table->string('title', 255);
            $table->string('board', 255);
            $table->string('url', 255);
            $table->integer('saves')->unsigned()->nullable()->default(0);
            $table->integer('comments')->unsigned()->nullable()->default(0);
            $table->string('way', 1)->nullable()->default('0');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::unprepared("
        create trigger trigger_pin_history after update on pins for each row
            begin
              declare i int;
              declare s int;
              declare c int;
              set i = (select count(*) from pin_data_history where pins_id = NEW.id and update_date > DATE_FORMAT(now(),'%Y-%m-%d'));
              if (i > 0) then
                select saves_change, comments_change into s, c from pin_data_history where pins_id = NEW.id and update_date > DATE_FORMAT(now(),'%Y-%m-%d');
                set s = s + NEW.saves;
                if (s < OLD.saves) then
                  set s = -(OLD.saves - s);
                else
                  set s = s - OLD.saves;
                end if;
                set c = c + NEW.comments;
                if (c < OLD.comments) then
                  set c = -(OLD.comments - c);
                else
                  set c = c - OLD.comments;
                end if;
                update pin_data_history set saves = NEW.saves, comments = NEW.comments, saves_change = s, comments_change = c where pins_id = NEW.id and update_date > DATE_FORMAT(now(),'%Y-%m-%d');
              else
                if (NEW.saves < OLD.saves) then
                  set s = -(OLD.saves - NEW.saves);
                else
                  set s = NEW.saves - OLD.saves;
                end if;
                if (NEW.comments < OLD.comments) then
                  set c = -(OLD.comments - NEW.comments);
                else
                  set c = NEW.comments - OLD.comments;
                end if;
                insert into pin_data_history(pins_id, saves, saves_change, comments, comments_change) values(NEW.id, NEW.saves, s, NEW.comments, c);
              end if;
            end
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('drop trigger if exists trigger_pin_history');
        Schema::drop('pins');
    }
}
